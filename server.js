// server.js
import express from "express";
import path2 from "path";
import { fileURLToPath } from "url";
import fs from "fs/promises";
import crypto from "crypto";
import pool from "./bd.js"; // <-- if in your repo the correct path is different, update this
// (e.g. if this file is placed inside another folder then change './bd.js' -> '../bd.js')

const __filename = fileURLToPath(import.meta.url);
const __dirname = path2.dirname(__filename);
const app = express();

// CORS middleware to allow requests from localhost (XAMPP)
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*'); // Allow all origins (you can restrict this to specific domains)
  res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
  
  // Handle preflight requests
  if (req.method === 'OPTIONS') {
    return res.sendStatus(200);
  }
  
  next();
});

// Allow larger payloads for image uploads (base64 up to ~10MB)
app.use(express.json({ limit: "10mb" }));
app.use(express.urlencoded({ extended: true, limit: "10mb" }));

// Serve static files from public directory (for both local and Vercel)
app.use(express.static(path2.join(__dirname, "public")));

/* =====================
   Health check
===================== */
app.get("/health", (req, res) => {
  res.json({ status: "ok" });
});

/* =====================
   USERS
===================== */
app.get("/users", async (req, res) => {
  try {
    const { search, estados, estatutos } = req.query;

    let sqlQuery = `
      SELECT 
        u.utilizador_id, 
        u.nome_utilizador, 
        u.email,
        e.nome_estado,
        e.hex_cor as estado_cor,
        f.nome_funcao as estatuto,
        u.funcao_id
      FROM utilizador u
      JOIN estado e ON u.estado_id = e.estado_id
      JOIN funcao f ON u.funcao_id = f.funcao_id
      WHERE 1=1
    `;

    const queryParams = [];
    let paramCounter = 1;

    if (search) {
      sqlQuery += ` AND (u.nome_utilizador ILIKE $${paramCounter} OR u.email ILIKE $${paramCounter + 1})`;
      queryParams.push(`%${search}%`);
      queryParams.push(`%${search}%`);
      paramCounter += 2;
    }
    if (estados) {
      const estadoArray = estados.split(',');
      sqlQuery += ` AND e.nome_estado = ANY($${paramCounter})`;
      queryParams.push(estadoArray);
      paramCounter++;
    }

    if (estatutos) {
      const estatutoArray = estatutos.split(',');
      sqlQuery += ` AND f.nome_funcao = ANY($${paramCounter})`;
      queryParams.push(estatutoArray);
      paramCounter++;
    }

    sqlQuery += ` ORDER BY u.utilizador_id`;

    const { rows } = await pool.query(sqlQuery, queryParams);
    res.json(rows);
  } catch (error) {
    console.error('Erro ao executar a query', error);
    res.status(500).send('Erro ao executar a query');
  }
});

// Get estado options for user filters (MUST be before /users/:id route)
app.get('/users/estados', async (req, res) => {
  try {
    const { rows } = await pool.query('SELECT nome_estado FROM estado ORDER BY estado_id');
    const estados = rows.map(row => row.nome_estado);
    res.json(estados);
  } catch (error) {
    console.error('Erro ao buscar estados:', error);
    res.status(500).send('Erro ao buscar estados');
  }
});

// Get estatuto (funcao) options for user filters (MUST be before /users/:id route)
app.get('/users/estatutos', async (req, res) => {
  try {
    const { rows } = await pool.query('SELECT nome_funcao FROM funcao ORDER BY funcao_id');
    const estatutos = rows.map(row => row.nome_funcao);
    res.json(estatutos);
  } catch (error) {
    console.error('Erro ao buscar estatutos:', error);
    res.status(500).send('Erro ao buscar estatutos');
  }
});

app.get('/users/:id', async (req, res) => {
  try {
    const { id } = req.params;
    
    if (!/^\d+$/.test(id)) {
      return res.status(400).json({ error: 'Invalid ID format. ID must be a number.' });
    }
    
    let sqlQuery = `
      SELECT
        u.utilizador_id,
        u.nome_utilizador,
        u.email,
        u.funcao_id,
        u.estado_id
      FROM
        utilizador AS u
      WHERE u.utilizador_id = $1;
    `;
  
    const { rows } = await pool.query(sqlQuery, [id]);
  
    if (rows.length === 0) {
      return res.status(404).json({ error: 'Utilizador not found' });
    }
    
    res.json(rows[0]);
  } catch (error) {
    console.error('Erro ao executar a query', error);
    res.status(500).send('Erro ao executar a query');
  }
});

/* =====================
   UPDATE USER PASSWORD (Must be before /users/:id to avoid route conflicts)
===================== */
app.put('/users/:id/password', async (req, res) => {
  try {
    const { id } = req.params;
    const { current_password, new_password } = req.body;

    if (!/^\d+$/.test(id)) {
      return res.status(400).json({ error: 'Invalid ID format. ID must be a number.' });
    }

    if (!current_password || !new_password) {
      return res.status(400).json({ error: 'Password atual e nova password são obrigatórios.' });
    }

    // Check if user exists and get current password hash
    const userCheck = await pool.query(
      'SELECT utilizador_id, password_hash FROM utilizador WHERE utilizador_id = $1',
      [id]
    );

    if (userCheck.rowCount === 0) {
      return res.status(404).json({ error: 'Utilizador not found' });
    }

    // Verify current password
    const currentPasswordHash = crypto.createHash('sha256').update(current_password).digest('hex');
    if (currentPasswordHash !== userCheck.rows[0].password_hash) {
      return res.status(401).json({ error: 'Password atual incorreta.' });
    }

    // Check if new password is different from current password
    if (current_password === new_password) {
      return res.status(400).json({ error: 'A nova password deve ser diferente da password atual.' });
    }

    // Hash new password
    const newPasswordHash = crypto.createHash('sha256').update(new_password).digest('hex');

    // Update password
    await pool.query(
      'UPDATE utilizador SET password_hash = $1 WHERE utilizador_id = $2',
      [newPasswordHash, id]
    );

    return res.status(200).json({
      message: 'Password atualizada com sucesso.',
      utilizador_id: parseInt(id)
    });
  } catch (error) {
    console.error('Erro ao atualizar password do utilizador', error);
    return res.status(500).json({ error: 'Erro ao atualizar password do utilizador.' });
  }
});

/* =====================
   UPDATE USER FUNCAO (Role)
===================== */
app.put('/users/:id/funcao', async (req, res) => {
  try {
    const { id } = req.params;
    const { funcao_id } = req.body;

    if (!/^\d+$/.test(id)) {
      return res.status(400).json({ error: 'Invalid ID format. ID must be a number.' });
    }

    if (!funcao_id || (funcao_id !== 1 && funcao_id !== 2)) {
      return res.status(400).json({ error: 'funcao_id must be 1 (Admin) or 2 (Utilizador).' });
    }

    // Check if user exists
    const userCheck = await pool.query(
      'SELECT utilizador_id FROM utilizador WHERE utilizador_id = $1',
      [id]
    );

    if (userCheck.rowCount === 0) {
      return res.status(404).json({ error: 'Utilizador not found' });
    }

    // Check if funcao exists
    const funcaoCheck = await pool.query(
      'SELECT funcao_id FROM funcao WHERE funcao_id = $1',
      [funcao_id]
    );

    if (funcaoCheck.rowCount === 0) {
      return res.status(400).json({ error: 'Funcao not found' });
    }

    // Update user funcao_id
    await pool.query(
      'UPDATE utilizador SET funcao_id = $1 WHERE utilizador_id = $2',
      [funcao_id, id]
    );

    // Get updated user data with funcao name
    const { rows } = await pool.query(
      `SELECT 
        u.utilizador_id,
        u.funcao_id,
        f.nome_funcao as estatuto
      FROM utilizador u
      JOIN funcao f ON u.funcao_id = f.funcao_id
      WHERE u.utilizador_id = $1`,
      [id]
    );

    return res.status(200).json({
      message: 'Funcao atualizada com sucesso.',
      utilizador_id: parseInt(id),
      funcao_id: funcao_id,
      estatuto: rows[0].estatuto
    });
  } catch (error) {
    console.error('Erro ao atualizar funcao do utilizador', error);
    return res.status(500).json({ error: 'Erro ao atualizar funcao do utilizador.' });
  }
});

/* =====================
   UPDATE USER ESTADO (Ban/Suspend/Normal)
   When user is banned (estado_id = 3), delete all their avistamentos
===================== */
app.put('/users/:id/estado', async (req, res) => {
  const client = await pool.connect();
  
  try {
    const { id } = req.params;
    const { estado_id } = req.body;

    if (!/^\d+$/.test(id)) {
      return res.status(400).json({ error: 'Invalid ID format. ID must be a number.' });
    }

    if (!estado_id || ![1, 2, 3].includes(Number(estado_id))) {
      return res.status(400).json({ error: 'estado_id must be 1 (Normal), 2 (Suspenso), or 3 (Banido).' });
    }

    try {
      await client.query('BEGIN');

      // Check if user exists
      const userCheck = await client.query(
        'SELECT utilizador_id FROM utilizador WHERE utilizador_id = $1',
        [id]
      );

      if (userCheck.rowCount === 0) {
        await client.query('ROLLBACK');
        return res.status(404).json({ error: 'Utilizador not found' });
      }

      // Check if estado exists
      const estadoCheck = await client.query(
        'SELECT estado_id FROM estado WHERE estado_id = $1',
        [estado_id]
      );

      if (estadoCheck.rowCount === 0) {
        await client.query('ROLLBACK');
        return res.status(400).json({ error: 'Estado not found' });
      }

      // If user is being banned (estado_id = 3), delete all their avistamentos
      if (Number(estado_id) === 3) {
        const deleteResult = await client.query(
          'DELETE FROM avistamento WHERE utilizador_id = $1',
          [id]
        );
        console.log(`Deleted ${deleteResult.rowCount} avistamentos for banned user ${id}`);
      }

      // Update user estado_id
      await client.query(
        'UPDATE utilizador SET estado_id = $1 WHERE utilizador_id = $2',
        [estado_id, id]
      );

      // Get updated user data with estado name
      const { rows } = await client.query(
        `SELECT 
          u.utilizador_id,
          u.estado_id,
          e.nome_estado,
          e.hex_cor as estado_cor
        FROM utilizador u
        JOIN estado e ON u.estado_id = e.estado_id
        WHERE u.utilizador_id = $1`,
        [id]
      );

      await client.query('COMMIT');

      return res.status(200).json({
        message: 'Estado atualizado com sucesso.',
        utilizador_id: parseInt(id),
        estado_id: Number(estado_id),
        nome_estado: rows[0].nome_estado,
        estado_cor: rows[0].estado_cor
      });
    } catch (error) {
      await client.query('ROLLBACK');
      console.error('Erro ao atualizar estado do utilizador (transaction):', error);
      return res.status(500).json({ error: 'Erro ao atualizar estado do utilizador.' });
    } finally {
      client.release();
    }
  } catch (error) {
    console.error('Erro ao atualizar estado do utilizador (outer):', error);
    if (client) client.release();
    return res.status(500).json({ error: 'Erro ao atualizar estado do utilizador.' });
  }
});

/* =====================
   UPDATE USER PROFILE (Nome e Email)
===================== */
app.put('/users/:id', async (req, res) => {
  try {
    const { id } = req.params;
    const { nome_utilizador, email } = req.body;

    if (!/^\d+$/.test(id)) {
      return res.status(400).json({ error: 'Invalid ID format. ID must be a number.' });
    }

    if (!nome_utilizador || !nome_utilizador.trim()) {
      return res.status(400).json({ error: 'Nome utilizador é obrigatório.' });
    }

    if (!email || !email.trim()) {
      return res.status(400).json({ error: 'Email é obrigatório.' });
    }

    // Basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.trim())) {
      return res.status(400).json({ error: 'Email inválido.' });
    }

    // Check if user exists
    const userCheck = await pool.query(
      'SELECT utilizador_id FROM utilizador WHERE utilizador_id = $1',
      [id]
    );

    if (userCheck.rowCount === 0) {
      return res.status(404).json({ error: 'Utilizador not found' });
    }

    // Check if email is already taken by another user
    const emailCheck = await pool.query(
      'SELECT utilizador_id FROM utilizador WHERE email = $1 AND utilizador_id != $2',
      [email.trim(), id]
    );

    if (emailCheck.rowCount > 0) {
      return res.status(409).json({ error: 'Email já está em uso por outro utilizador.' });
    }

    // Update user profile
    await pool.query(
      'UPDATE utilizador SET nome_utilizador = $1, email = $2 WHERE utilizador_id = $3',
      [nome_utilizador.trim(), email.trim(), id]
    );

    // Get updated user data
    const { rows } = await pool.query(
      `SELECT 
        u.utilizador_id,
        u.nome_utilizador,
        u.email,
        u.funcao_id,
        u.estado_id
      FROM utilizador u
      WHERE u.utilizador_id = $1`,
      [id]
    );

    return res.status(200).json({
      message: 'Perfil atualizado com sucesso.',
      utilizador_id: parseInt(id),
      nome_utilizador: rows[0].nome_utilizador,
      email: rows[0].email
    });
  } catch (error) {
    console.error('Erro ao atualizar perfil do utilizador', error);
    return res.status(500).json({ error: 'Erro ao atualizar perfil do utilizador.' });
  }
});

/* =====================
   DELETE USER
   Allow a user to delete their own account or an admin to delete any account
===================== */
app.delete('/users/:id', async (req, res) => {
  try {
    const { id } = req.params;
    const { utilizador_id, funcao_id } = req.body;

    if (!/^[0-9]+$/.test(id)) {
      return res.status(400).json({ error: 'Invalid ID format. ID must be a number.' });
    }

    if (!utilizador_id || !funcao_id) {
      return res.status(401).json({ error: 'Autenticação necessária.' });
    }

    // Check target user exists
    const userCheck = await pool.query('SELECT utilizador_id FROM utilizador WHERE utilizador_id = $1', [id]);
    if (userCheck.rowCount === 0) {
      return res.status(404).json({ error: 'Utilizador não encontrado.' });
    }

    const isAdmin = Number(funcao_id) === 1;
    const isOwner = Number(utilizador_id) === Number(id);

    if (!isAdmin && !isOwner) {
      return res.status(403).json({ error: 'Não tem permissão para eliminar este utilizador.' });
    }

    await pool.query('DELETE FROM utilizador WHERE utilizador_id = $1', [id]);

    return res.status(200).json({ message: 'Utilizador eliminado com sucesso.' });
  } catch (error) {
    console.error('Erro ao eliminar utilizador', error);
    return res.status(500).json({ error: 'Erro ao eliminar utilizador.' });
  }
});

/* =====================
   ANIMAIS
===================== */

// Get all animal families (MUST be before /animais/:id if such route exists)
app.get('/animais/familias', async (req, res) => {
  try {
    const { rows } = await pool.query(
      'SELECT familia_id, TRIM(nome_familia) as nome_familia FROM familia ORDER BY nome_familia'
    );
    res.json(rows);
  } catch (error) {
    console.error('Erro ao buscar famílias de animais:', error);
    res.status(500).json({ error: 'Erro ao buscar famílias de animais.' });
  }
});

// Get all conservation statuses (MUST be before /animais/:id if such route exists)
app.get('/animais/estados', async (req, res) => {
  try {
    const { rows } = await pool.query(
      'SELECT estado_id, TRIM(nome_estado) as nome_estado, hex_cor FROM estado_conservacao ORDER BY estado_id'
    );
    res.json(rows);
  } catch (error) {
    console.error('Erro ao buscar estados de conservação:', error);
    res.status(500).json({ error: 'Erro ao buscar estados de conservação.' });
  }
});

app.get('/animais', async (req, res) => {
  try {
    const { search, families, states } = req.query;
    
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

    if (search) {
      sqlQuery += ` AND (a.nome_comum ILIKE $${paramCounter})`;
      queryParams.push(`%${search}%`);
      paramCounter++;
    }

    if (families) {
      const familyArray = families.split(',');
      sqlQuery += ` AND f.nome_familia = ANY($${paramCounter})`;
      queryParams.push(familyArray);
      paramCounter++;
    }

    if (states) {
      const stateArray = states.split(',');
      sqlQuery += ` AND e.nome_estado = ANY($${paramCounter})`;
      queryParams.push(stateArray);
      paramCounter++;
    }

    sqlQuery += ` ORDER BY a.animal_id`;

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
    
    if (!/^\d+$/.test(id)) {
      return res.status(400).json({ error: 'Invalid ID format. ID must be a number.' });
    }
    
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

    let sqlQueryAmeacas = `
      SELECT a.descricao
      FROM ameaca a 
      JOIN animal_ameaca aa ON a.ameaca_id = aa.ameaca_id 
      WHERE aa.animal_id = $1
    `;

    const [animalResult, ameacasResult] = await Promise.all([
      pool.query(sqlQuery, [id]),
      pool.query(sqlQueryAmeacas, [id])
    ]);

    if (animalResult.rows.length === 0) {
      return res.status(404).json({ error: 'Animal not found' });
    }

    const ameacasList = ameacasResult.rows.map(ameaca => ameaca.descricao);

    const finalData = {
      ...animalResult.rows[0],
      ameacas: ameacasList
    };

    res.json(finalData);

  } catch (error) {
    console.error('Erro ao executar a query', error);
    res.status(500).send('Erro ao executar a query');
  }
});

/* =====================
   CREATE ANIMAL (NO FILESYSTEM)
   Expects imagem_url (external storage)
===================== */
app.post('/animais', async (req, res) => {
  const {
    nome_comum,
    nome_cientifico,
    descricao,
    facto_interessante,
    populacao_estimada,
    familia_nome,
    dieta_nome,
    estado_nome,
    ameacas = [],
    imagem_url
  } = req.body || {};

  // Validate all required fields and collect errors
  const errors = [];
  if (!nome_comum || !nome_comum.trim()) errors.push('Nome comum é obrigatório.');
  if (!nome_cientifico || !nome_cientifico.trim()) errors.push('Nome científico é obrigatório.');
  if (!descricao || !descricao.trim()) errors.push('Descrição é obrigatória.');
  if (!familia_nome || !familia_nome.trim()) errors.push('Família é obrigatória.');
  if (!dieta_nome || !dieta_nome.trim()) errors.push('Dieta é obrigatória.');
  if (!estado_nome || !estado_nome.trim()) errors.push('Estado de conservação é obrigatório.');
  if (!imagem_url || !imagem_url.trim()) errors.push('Imagem URL é obrigatória.');
  
  if (errors.length > 0) {
    return res.status(400).json({ error: errors.join(' ') });
  }

  const normalizedPopulation =
    typeof populacao_estimada === 'number'
      ? populacao_estimada
      : Number(String(populacao_estimada || '').replace(/[^\d]/g, '')) || null;

  const client = await pool.connect();

  try {
    await client.query('BEGIN');

    // Use TRIM in the query to handle any trailing spaces in database
    const familia = await client.query(
      'SELECT familia_id FROM familia WHERE TRIM(nome_familia) = TRIM($1) LIMIT 1',
      [familia_nome.trim()]
    );
    if (familia.rowCount === 0) {
      await client.query('ROLLBACK');
      const allFamilias = await client.query('SELECT TRIM(nome_familia) as nome_familia FROM familia ORDER BY familia_id');
      const availableFamilias = allFamilias.rows.map(r => r.nome_familia).join(', ');
      return res.status(400).json({ error: `Família "${familia_nome.trim()}" não encontrada na base de dados. Famílias disponíveis: ${availableFamilias}` });
    }

    const dieta = await client.query(
      'SELECT dieta_id FROM dieta WHERE TRIM(nome_dieta) = TRIM($1) LIMIT 1',
      [dieta_nome.trim()]
    );
    if (dieta.rowCount === 0) {
      await client.query('ROLLBACK');
      const allDietas = await client.query('SELECT TRIM(nome_dieta) as nome_dieta FROM dieta ORDER BY dieta_id');
      const availableDietas = allDietas.rows.map(r => r.nome_dieta).join(', ');
      return res.status(400).json({ error: `Dieta "${dieta_nome.trim()}" não encontrada na base de dados. Dietas disponíveis: ${availableDietas}` });
    }

    // Use TRIM in the query to handle trailing spaces in database
    const estado = await client.query(
      'SELECT estado_id FROM estado_conservacao WHERE TRIM(nome_estado) = TRIM($1) LIMIT 1',
      [estado_nome.trim()]
    );
    if (estado.rowCount === 0) {
      await client.query('ROLLBACK');
      // Try to get available states for better error message
      const allStates = await client.query('SELECT TRIM(nome_estado) as nome_estado FROM estado_conservacao ORDER BY estado_id');
      const availableStates = allStates.rows.map(r => r.nome_estado).join(', ');
      return res.status(400).json({ 
        error: `Estado de conservação "${estado_nome.trim()}" não encontrado na base de dados. Estados disponíveis: ${availableStates}` 
      });
    }

    const insertAnimal = await client.query(
      `INSERT INTO animal (
        nome_comum,
        nome_cientifico,
        descricao,
        facto_interessante,
        populacao_estimada,
        url_imagem,
        contagem_vistas,
        dieta_id,
        familia_id,
        estado_id
      )
      VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10)
      RETURNING animal_id`,
      [
        nome_comum,
        nome_cientifico,
        descricao,
        facto_interessante || '',
        normalizedPopulation,
        imagem_url,
        0,
        dieta.rows[0].dieta_id,
        familia.rows[0].familia_id,
        estado.rows[0].estado_id
      ]
    );

    const animalId = insertAnimal.rows[0].animal_id;

    const uniqueThreats = Array.from(
      new Set(
        (ameacas || [])
          .map((t) => (t || '').trim())
          .filter((t) => t.length > 0)
          .slice(0, 5)
      )
    );

    for (const threat of uniqueThreats) {
      let threatId;
      const existing = await client.query('SELECT ameaca_id FROM ameaca WHERE descricao = $1 LIMIT 1', [
        threat
      ]);
      if (existing.rowCount > 0) {
        threatId = existing.rows[0].ameaca_id;
      } else {
        const inserted = await client.query(
          'INSERT INTO ameaca (descricao) VALUES ($1) RETURNING ameaca_id',
          [threat]
        );
        threatId = inserted.rows[0].ameaca_id;
      }

      await client.query(
        'INSERT INTO animal_ameaca (animal_id, ameaca_id) VALUES ($1, $2) ON CONFLICT DO NOTHING',
        [animalId, threatId]
      );
    }

    await client.query('COMMIT');

    return res.status(201).json({
      message: 'Animal criado com sucesso.',
      animal_id: animalId,
      url_imagem: imagem_url
    });
  } catch (error) {
    await client.query('ROLLBACK');
    console.error('Erro ao criar animal', error);
    return res.status(500).json({ error: 'Erro ao criar animal.' });
  } finally {
    client.release();
  }
});

/* =====================
   UPDATE ANIMAL
   (Updates animal info, excluding image)
===================== */
app.put('/animais/:id', async (req, res) => {
  const {
    nome_comum,
    nome_cientifico,
    descricao,
    facto_interessante,
    populacao_estimada,
    familia_nome,
    dieta_nome,
    estado_nome,
    ameacas = []
  } = req.body || {};

  const { id } = req.params;

  if (!/^\d+$/.test(id)) {
    return res.status(400).json({ error: 'Invalid ID format. ID must be a number.' });
  }

  // Validate all required fields and collect errors
  const errors = [];
  if (!nome_comum || !nome_comum.trim()) errors.push('Nome comum é obrigatório.');
  if (!nome_cientifico || !nome_cientifico.trim()) errors.push('Nome científico é obrigatório.');
  if (!descricao || !descricao.trim()) errors.push('Descrição é obrigatória.');
  if (!familia_nome || !familia_nome.trim()) errors.push('Família é obrigatória.');
  if (!dieta_nome || !dieta_nome.trim()) errors.push('Dieta é obrigatória.');
  if (!estado_nome || !estado_nome.trim()) errors.push('Estado de conservação é obrigatório.');
  
  if (errors.length > 0) {
    return res.status(400).json({ error: errors.join(' ') });
  }

  const normalizedPopulation =
    typeof populacao_estimada === 'number'
      ? populacao_estimada
      : Number(String(populacao_estimada || '').replace(/[^\d]/g, '')) || null;

  const client = await pool.connect();

  try {
    await client.query('BEGIN');

    // Check if animal exists
    const animalCheck = await client.query(
      'SELECT animal_id FROM animal WHERE animal_id = $1',
      [id]
    );
    if (animalCheck.rowCount === 0) {
      await client.query('ROLLBACK');
      return res.status(404).json({ error: 'Animal não encontrado.' });
    }

    // Use TRIM in the query to handle any trailing spaces in database
    const familia = await client.query(
      'SELECT familia_id FROM familia WHERE TRIM(nome_familia) = TRIM($1) LIMIT 1',
      [familia_nome.trim()]
    );
    if (familia.rowCount === 0) {
      await client.query('ROLLBACK');
      const allFamilias = await client.query('SELECT TRIM(nome_familia) as nome_familia FROM familia ORDER BY familia_id');
      const availableFamilias = allFamilias.rows.map(r => r.nome_familia).join(', ');
      return res.status(400).json({ error: `Família "${familia_nome.trim()}" não encontrada na base de dados. Famílias disponíveis: ${availableFamilias}` });
    }

    const dieta = await client.query(
      'SELECT dieta_id FROM dieta WHERE TRIM(nome_dieta) = TRIM($1) LIMIT 1',
      [dieta_nome.trim()]
    );
    if (dieta.rowCount === 0) {
      await client.query('ROLLBACK');
      const allDietas = await client.query('SELECT TRIM(nome_dieta) as nome_dieta FROM dieta ORDER BY dieta_id');
      const availableDietas = allDietas.rows.map(r => r.nome_dieta).join(', ');
      return res.status(400).json({ error: `Dieta "${dieta_nome.trim()}" não encontrada na base de dados. Dietas disponíveis: ${availableDietas}` });
    }

    // Use TRIM in the query to handle trailing spaces in database
    const estado = await client.query(
      'SELECT estado_id FROM estado_conservacao WHERE TRIM(nome_estado) = TRIM($1) LIMIT 1',
      [estado_nome.trim()]
    );
    if (estado.rowCount === 0) {
      await client.query('ROLLBACK');
      const allStates = await client.query('SELECT TRIM(nome_estado) as nome_estado FROM estado_conservacao ORDER BY estado_id');
      const availableStates = allStates.rows.map(r => r.nome_estado).join(', ');
      return res.status(400).json({ 
        error: `Estado de conservação "${estado_nome.trim()}" não encontrado na base de dados. Estados disponíveis: ${availableStates}` 
      });
    }

    // Update animal (excluding image URL)
    await client.query(
      `UPDATE animal SET
        nome_comum = $1,
        nome_cientifico = $2,
        descricao = $3,
        facto_interessante = $4,
        populacao_estimada = $5,
        dieta_id = $6,
        familia_id = $7,
        estado_id = $8
      WHERE animal_id = $9`,
      [
        nome_comum,
        nome_cientifico,
        descricao,
        facto_interessante || '',
        normalizedPopulation,
        dieta.rows[0].dieta_id,
        familia.rows[0].familia_id,
        estado.rows[0].estado_id,
        id
      ]
    );

    // Handle ameacas (threats) - delete existing relationships and insert new
    try {
      await client.query('DELETE FROM animal_ameaca WHERE animal_id = $1', [id]);
      
      if (Array.isArray(ameacas) && ameacas.length > 0) {
        const uniqueThreats = Array.from(
          new Set(
            ameacas
              .map((t) => (t || '').trim())
              .filter((t) => t.length > 0)
              .slice(0, 5)
          )
        );

        for (const threat of uniqueThreats) {
          let threatId;
          const existing = await client.query('SELECT ameaca_id FROM ameaca WHERE descricao = $1 LIMIT 1', [
            threat
          ]);
          if (existing.rowCount > 0) {
            threatId = existing.rows[0].ameaca_id;
          } else {
            const inserted = await client.query(
              'INSERT INTO ameaca (descricao) VALUES ($1) RETURNING ameaca_id',
              [threat]
            );
            threatId = inserted.rows[0].ameaca_id;
          }

          await client.query(
            'INSERT INTO animal_ameaca (animal_id, ameaca_id) VALUES ($1, $2) ON CONFLICT DO NOTHING',
            [id, threatId]
          );
        }
      }
    } catch (ameacaError) {
      console.error('Erro ao processar ameacas:', ameacaError);
      throw new Error(`Erro ao processar ameacas: ${ameacaError.message}`);
    }

    await client.query('COMMIT');

    return res.status(200).json({ message: 'Animal atualizado com sucesso.' });
  } catch (error) {
    await client.query('ROLLBACK').catch(rollbackError => {
      console.error('Erro ao fazer rollback:', rollbackError);
    });
    console.error('Erro ao atualizar animal:', error);
    console.error('Error details:', {
      message: error.message,
      stack: error.stack,
      code: error.code
    });
    return res.status(500).json({ 
      error: 'Erro ao atualizar animal.',
      details: error.message 
    });
  } finally {
    client.release();
  }
});

/* =====================
   DELETE ANIMAL
   (Deletes DB row and image file from server)
===================== */
app.delete('/animais/:id', async (req, res) => {
  const client = await pool.connect();
  
  try {
    const { id } = req.params;
    
    if (!/^\d+$/.test(id)) {
      return res.status(400).json({ error: 'Invalid ID format. ID must be a number.' });
    }

    try {
      await client.query('BEGIN');

      const animalResult = await client.query(
        'SELECT url_imagem FROM animal WHERE animal_id = $1',
        [id]
      );

      if (animalResult.rowCount === 0) {
        await client.query('ROLLBACK');
        return res.status(404).json({ error: 'Animal not found.' });
      }

      const imageUrl = animalResult.rows[0].url_imagem;

      // Delete from database first
      await client.query('DELETE FROM animal WHERE animal_id = $1', [id]);

      await client.query('COMMIT');

      // After successful DB deletion, try to delete the image file from Hostinger
      if (imageUrl && imageUrl.trim() !== '') {
        try {
          console.log(`[DELETE IMAGE] Attempting to delete image: ${imageUrl}`);
          
          // Call PHP endpoint to delete the image with timeout
          const controller = new AbortController();
          const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
          
          try {
            const deleteImageResponse = await fetch('https://biomappt.com/public/delete_image.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({ image_url: imageUrl }),
              signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            let responseData;
            try {
              responseData = await deleteImageResponse.json();
            } catch (parseError) {
              const textResponse = await deleteImageResponse.text();
              console.error('[DELETE IMAGE] Failed to parse JSON response:', textResponse);
              throw new Error(`Invalid JSON response: ${textResponse.substring(0, 100)}`);
            }
            
            if (!deleteImageResponse.ok) {
              console.error('[DELETE IMAGE] Error deleting image:', responseData.error || 'Unknown error', `Status: ${deleteImageResponse.status}`);
            } else {
              console.log('[DELETE IMAGE] Image deleted successfully:', responseData.message || 'Success');
            }
          } catch (fetchError) {
            clearTimeout(timeoutId);
            if (fetchError.name === 'AbortError') {
              console.error('[DELETE IMAGE] Request timeout after 10 seconds');
            } else {
              throw fetchError; // Re-throw to be caught by outer catch
            }
          }
        } catch (imageError) {
          // Log error but don't fail the request since DB deletion succeeded
          console.error('[DELETE IMAGE] Error deleting image (network/parse error):', imageError.message || imageError);
        }
      } else {
        console.log('[DELETE IMAGE] No image to delete (url_imagem is empty or null)');
      }

      return res.status(200).json({ message: 'Animal deletado com sucesso.' });
    } catch (error) {
      await client.query('ROLLBACK');
      console.error('Erro ao deletar animal (transaction):', error);
      return res.status(500).json({ error: 'Erro ao deletar animal.' });
    } finally {
      client.release();
    }
  } catch (error) {
    console.error('Erro ao deletar animal (outer):', error);
    if (client) client.release();
    return res.status(500).json({ error: 'Erro ao deletar animal.' });
  }
});

/* =====================
   AUTH
===================== */
// Check if user name or email already exists
app.post('/api/check-user', async (req, res) => {
  try {
    const { name, email } = req.body;

    if (!name && !email) {
      return res.status(400).json({ error: 'Nome ou email é obrigatório.' });
    }

    let nameExists = false;
    let emailExists = false;

    if (name) {
      const nameCheck = await pool.query(
        'SELECT utilizador_id FROM utilizador WHERE nome_utilizador = $1',
        [name.trim()]
      );
      nameExists = nameCheck.rowCount > 0;
    }

    if (email) {
      const emailCheck = await pool.query(
        'SELECT utilizador_id FROM utilizador WHERE email = $1',
        [email.trim()]
      );
      emailExists = emailCheck.rowCount > 0;
    }

    return res.status(200).json({
      nameExists,
      emailExists
    });
  } catch (error) {
    console.error('Erro ao verificar utilizador', {
      message: error?.message,
      code: error?.code,
      detail: error?.detail,
    });
    return res.status(500).json({ error: 'Erro ao verificar utilizador.' });
  }
});

app.post('/api/signup', async (req, res) => {
  try {
    const { name, email, password } = req.body;

    if (!name || !email || !password) {
      return res.status(400).json({ error: 'Nome, email e password são obrigatórios.' });
    }

    // Check if name already exists
    const existingName = await pool.query(
      'SELECT utilizador_id FROM utilizador WHERE nome_utilizador = $1',
      [name.trim()]
    );

    // Check if email already exists
    const existingEmail = await pool.query(
      'SELECT utilizador_id FROM utilizador WHERE email = $1',
      [email.trim()]
    );

    const nameExists = existingName.rowCount > 0;
    const emailExists = existingEmail.rowCount > 0;

    if (nameExists && emailExists) {
      return res.status(409).json({ error: 'Nome e email já existentes', nameExists: true, emailExists: true });
    }

    if (nameExists) {
      return res.status(409).json({ error: 'Este nome já existe', nameExists: true, emailExists: false });
    }

    if (emailExists) {
      return res.status(409).json({ error: 'Este email já existe', nameExists: false, emailExists: true });
    }

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

    const { rows } = await pool.query(insertQuery, [name.trim(), email.trim(), passwordHash]);

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

/* =====================
   ALERTS (avistamentos)
   GET /api/alerts
   POST /api/alerts
   DELETE /api/alerts/:id
===================== */
app.get('/api/alerts', async (req, res) => {
  try {
    const { search, families, states } = req.query;
    
    let sqlQuery = `
      SELECT 
        av.avistamento_id,
        av.data_avistamento,
        av.utilizador_id,
        ST_Y(av."localização"::geometry) as latitude,
        ST_X(av."localização"::geometry) as longitude,
        a.animal_id,
        a.nome_comum,
        a.nome_cientifico,
        a.descricao,
        a.url_imagem,
        f.nome_familia,
        d.nome_dieta,
        e.nome_estado,
        e.hex_cor as estado_cor
      FROM avistamento av
      JOIN animal a ON av.animal_id = a.animal_id
      JOIN familia f ON a.familia_id = f.familia_id
      JOIN estado_conservacao e ON a.estado_id = e.estado_id
      LEFT JOIN dieta d ON a.dieta_id = d.dieta_id
      WHERE 1=1
    `;

    const queryParams = [];
    let paramCounter = 1;

    if (search) {
      sqlQuery += ` AND (a.nome_comum ILIKE $${paramCounter})`;
      queryParams.push(`%${search}%`);
      paramCounter++;
    }

    if (families) {
      const familyArray = families.split(',');
      sqlQuery += ` AND f.nome_familia = ANY($${paramCounter})`;
      queryParams.push(familyArray);
      paramCounter++;
    }

    if (states) {
      const stateArray = states.split(',');
      sqlQuery += ` AND e.nome_estado = ANY($${paramCounter})`;
      queryParams.push(stateArray);
      paramCounter++;
    }

    sqlQuery += ` ORDER BY av.data_avistamento DESC`;

    const { rows } = await pool.query(sqlQuery, queryParams);
    res.json(rows);
  } catch (error) {
    console.error('Erro ao buscar avistamentos', error);
    res.status(500).json({ error: 'Erro ao buscar avistamentos.' });
  }
});

app.post('/api/alerts', async (req, res) => {
  try {
    const { animal_id, utilizador_id, latitude, longitude, data_avistamento } = req.body;

    // Validate required fields
    if (!animal_id || !utilizador_id || latitude === undefined || longitude === undefined) {
      return res.status(400).json({ error: 'Campos obrigatórios em falta: animal_id, utilizador_id, latitude, longitude.' });
    }

    // Validate that utilizador_id is provided and valid (user must be logged in)
    if (!utilizador_id || utilizador_id === 'null' || utilizador_id === 'undefined') {
      return res.status(401).json({ error: 'Deve iniciar sessão para criar um alerta.' });
    }

    if (!/^\d+$/.test(String(animal_id)) || !/^\d+$/.test(String(utilizador_id))) {
      return res.status(400).json({ error: 'IDs devem ser números válidos.' });
    }

    const lat = parseFloat(latitude);
    const lon = parseFloat(longitude);
    if (isNaN(lat) || isNaN(lon) || lat < -90 || lat > 90 || lon < -180 || lon > 180) {
      return res.status(400).json({ error: 'Coordenadas inválidas.' });
    }

    const animalCheck = await pool.query('SELECT animal_id FROM animal WHERE animal_id = $1', [animal_id]);
    if (animalCheck.rowCount === 0) {
      return res.status(404).json({ error: 'Animal não encontrado.' });
    }

    const userCheck = await pool.query('SELECT utilizador_id FROM utilizador WHERE utilizador_id = $1', [utilizador_id]);
    if (userCheck.rowCount === 0) {
      return res.status(404).json({ error: 'Utilizador não encontrado.' });
    }

    const avistamentoDate = data_avistamento || new Date().toISOString();

    const insertQuery = `
      INSERT INTO avistamento (
        data_avistamento,
        "localização",
        animal_id,
        utilizador_id
      )
      VALUES ($1, ST_SetSRID(ST_MakePoint($2, $3), 4326)::geography, $4, $5)
      RETURNING avistamento_id
    `;

    const { rows } = await pool.query(insertQuery, [
      avistamentoDate,
      lon,
      lat,
      animal_id,
      utilizador_id
    ]);

    return res.status(201).json({
      message: 'Alerta criado com sucesso.',
      avistamento_id: rows[0].avistamento_id
    });
  } catch (error) {
    console.error('Erro ao criar alerta', error);
    return res.status(500).json({ error: 'Erro ao criar alerta.' });
  }
});

app.delete('/api/alerts/:id', async (req, res) => {
  try {
    const { id } = req.params;
    const { utilizador_id, funcao_id } = req.body;

    if (!/^\d+$/.test(id)) {
      return res.status(400).json({ error: 'Invalid ID format. ID must be a number.' });
    }

    if (!utilizador_id || !funcao_id) {
      return res.status(401).json({ error: 'Autenticação necessária.' });
    }

    const avistamentoCheck = await pool.query(
      'SELECT utilizador_id FROM avistamento WHERE avistamento_id = $1',
      [id]
    );

    if (avistamentoCheck.rowCount === 0) {
      return res.status(404).json({ error: 'Avistamento não encontrado.' });
    }

    const creatorId = avistamentoCheck.rows[0].utilizador_id;
    const isAdmin = Number(funcao_id) === 1;
    const isCreator = Number(utilizador_id) === Number(creatorId);

    if (!isAdmin && !isCreator) {
      return res.status(403).json({ error: 'Não tem permissão para eliminar este avistamento.' });
    }

    await pool.query('DELETE FROM avistamento WHERE avistamento_id = $1', [id]);

    return res.status(200).json({ message: 'Avistamento eliminado com sucesso.' });
  } catch (error) {
    console.error('Erro ao eliminar avistamento', error);
    return res.status(500).json({ error: 'Erro ao eliminar avistamento.' });
  }
});

/* =====================
   LOCAL LISTEN (only when run directly)
   and EXPORT app so it can be imported by Vercel
===================== */

// Start the server only if this file is the main module run by Node (i.e. `node server.js`)
if (process.argv[1] === fileURLToPath(import.meta.url)) {
  const PORT = process.env.PORT || 3000;
  app.listen(PORT, () => console.log(`A correr na porta http://localhost:${PORT}`));
}

// Export the Express app so other modules (like api/index.js on Vercel) can import it
export default app;
