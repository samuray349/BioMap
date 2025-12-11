
import express from "express"
import path2 from "path";
import { fileURLToPath } from "url"
import { appendFile } from "fs";
import crypto from "crypto";
import pool from './bd.js';
const __filename = fileURLToPath(import.meta.url);
const __dirname = path2.dirname(__filename);
const app = express();
const PORT = process.env.PORT || 3000;

// Quick connectivity probe on startup to surface DB issues early
(async () => {
  try {
    await pool.query("SELECT 1");
    console.log("DB connection OK");
  } catch (err) {
    console.error("DB connection failed:", err);
  }
})();

app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path2.join(__dirname, "public")));

app.get("/", (req, res) => {
  res.sendFile(path2.join(__dirname, "public", "index.html"));
});
app.get('/users', async (req, res) => {
    try {
      const { rows } = await pool.query('SELECT utilizador_id, nome_utilizador, email FROM utilizador ORDER BY utilizador_id');
      res.json(rows);
    } catch (error) {
      console.error('Erro ao executar a query', error);
      res.status(500).send('Erro da base de dados');
    }
  });

  app.get('/animais', async (req, res) => {
    try {
        const { search, families, states } = req.query;
        
        // 1. Base Query: Join tables to get readable names (Family and Status)
        // We use LEFT JOIN to ensure we still get animals even if a relation is missing (though your DB constraints prevent this)
        let sqlQuery = `
            SELECT 
                a.animal_id, 
                a.nome_comum, 
                a.nome_cientifico, 
                a.descricao, 
                a.url_imagem, 
                f.nome_familia, 
                e.nome_estado, 
                e.hex_cor as estado_cor
            FROM animal a
            JOIN familia f ON a.familia_id = f.familia_id
            JOIN estado_conservacao e ON a.estado_id = e.estado_id
            WHERE 1=1
        `;

        const queryParams = [];
        let paramCounter = 1;

        // 2. Text Search Filter (Case insensitive ILIKE on Name or Description)
        if (search) {
            sqlQuery += ` AND (a.nome_comum ILIKE $${paramCounter})`;
            queryParams.push(`%${search}%`);
            paramCounter++;
        }

        // 3. Family Filter (Matches 'familia' table)
        // Expecting 'families' to be a comma-separated string like "Felidae,Canidae"
        if (families) {
            const familyArray = families.split(',');
            sqlQuery += ` AND f.nome_familia = ANY($${paramCounter})`;
            queryParams.push(familyArray);
            paramCounter++;
        }

        // 4. Conservation Status Filter (Matches 'estado_conservacao' table)
        if (states) {
            const stateArray = states.split(',');
            sqlQuery += ` AND e.nome_estado = ANY($${paramCounter})`;
            queryParams.push(stateArray);
            paramCounter++;
        }

        sqlQuery += ` ORDER BY a.animal_id`;

        // Execute Query
        const { rows } = await pool.query(sqlQuery, queryParams);
        res.json(rows);

    } catch (error) {
        console.error('Erro ao executar a query', error);
        res.status(500).send('Erro ao executar a query');
    }
});
app.get('/animaisDesc/:id', async (req, res) => {
  try {
      const { id } = req.params;
      
      // Validar ID
      if (!/^\d+$/.test(id)) {
        return res.status(400).json({ error: 'Invalid ID format. ID must be a number.' });
   }
   
   // Query para detalhes do animal
   let sqlQuery = `
       SELECT 
           a.animal_id, 
           a.nome_comum, 
           a.nome_cientifico, 
           a.descricao, 
           a.url_imagem, 
           a.populacao_estimada,
           a.facto_interessante,
           f.nome_familia, 
           d.nome_dieta,
           e.nome_estado, 
           e.hex_cor as estado_cor
       FROM animal a
       LEFT JOIN familia f ON a.familia_id = f.familia_id
       LEFT JOIN estado_conservacao e ON a.estado_id = e.estado_id
       LEFT JOIN dieta d ON a.dieta_id = d.dieta_id
       WHERE a.animal_id = $1
   `;

   // Query para ameacas
   let sqlQueryAmeacas = `
       SELECT a.descricao
       FROM ameaca a 
       JOIN animal_ameaca aa ON a.ameaca_id = aa.ameaca_id 
       WHERE aa.animal_id = $1
   `;

   const queryParams = [id]; 
   
   const [animalResult, ameacasResult] = await Promise.all([
       pool.query(sqlQuery, queryParams),
       pool.query(sqlQueryAmeacas, queryParams)
   ]);

   const rows = animalResult.rows;

   const ameacas = ameacasResult.rows; 

   if (rows.length === 0) {
       return res.status(404).json({ error: 'Animal not found' });
   }
   
   // Extrair a descrição das ameacas
   const ameacasList = ameacas.map(ameaca => ameaca.descricao);

   // Combinar os dados do animal e das ameacas
   const finalData = {
       ...rows[0],
       // 'ameacas' é agora uma lista de strings
       ameacas: ameacasList 
   };
console.log(finalData);
   res.json(finalData);

  } catch (error) {
      console.error('Erro ao executar a query', error);
      res.status(500).send('Erro ao executar a query');
  }
});

app.post('/api/signup', async (req, res) => {
  try {
    const { name, email, password } = req.body;

    if (!name || !email || !password) {
      return res.status(400).json({ error: 'Nome, email e password são obrigatórios.' });
    }

    // Verificar se o utilizador já existe pelo email
    const existingUser = await pool.query(
      'SELECT utilizador_id FROM utilizador WHERE email = $1',
      [email]
    );

    if (existingUser.rowCount > 0) {
      return res.status(409).json({ error: 'Email já registado. Inicie sessão ou utilize outro email.' });
    }

    // Hash simples da password (SHA-256). Pode ser trocado por bcrypt se necessário.
    const passwordHash = crypto.createHash('sha256').update(password).digest('hex');

    const insertQuery = `
      INSERT INTO utilizador (
        nome_utilizador,
        email,
        password_hash,
        funcao_id,
        estado_id,
        data_criacao
      )
      VALUES ($1, $2, $3, 2, 1, NOW())
      RETURNING utilizador_id
    `;

    const { rows } = await pool.query(insertQuery, [name, email, passwordHash]);

    return res.status(201).json({
      message: 'Utilizador criado com sucesso.',
      utilizador_id: rows[0]?.utilizador_id
    });
  } catch (error) {
    console.error('Erro ao criar utilizador', {
      message: error?.message,
      code: error?.code,
      detail: error?.detail,
    });
    return res.status(500).json({ error: 'Erro ao criar utilizador.' });
  }
});

app.post('/api/login', async (req, res) => {
  try {
    const { email, password } = req.body;

    if (!email || !password) {
      return res.status(400).json({ error: 'Email e password são obrigatórios.' });
    }

    const { rows } = await pool.query(
      'SELECT utilizador_id, nome_utilizador, email, password_hash, estado_id, funcao_id FROM utilizador WHERE email = $1 LIMIT 1',
      [email]
    );

    if (rows.length === 0) {
      return res.status(401).json({ error: 'Credenciais inválidas.' });
    }

    const user = rows[0];
    if (Number(user.estado_id) !== 1) {
      return res.status(403).json({ error: 'Conta inativa.' });
    }

    const passwordHash = crypto.createHash('sha256').update(password).digest('hex');
    if (passwordHash !== user.password_hash) {
      return res.status(401).json({ error: 'Credenciais inválidas.' });
    }

    return res.status(200).json({
      message: 'Login bem-sucedido.',
      user: {
        id: user.utilizador_id,
        name: user.nome_utilizador,
        email: user.email,
        funcao_id: user.funcao_id
      }
    });
  } catch (error) {
    console.error('Erro ao iniciar sessão', {
      message: error?.message,
      code: error?.code,
      detail: error?.detail,
    });
    return res.status(500).json({ error: 'Erro ao iniciar sessão.' });
  }
});

app.listen(PORT, () => console.log(`A correr na porta http://localhost:${PORT}`));
