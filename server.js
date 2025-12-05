
import express from "express"
import path2 from "path";
import { fileURLToPath } from "url"
import { appendFile } from "fs";
import pool from './bd.js';
const __filename = fileURLToPath(import.meta.url);
const __dirname = path2.dirname(__filename);
const app = express();
const PORT = process.env.PORT || 3000;

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
        console.log(sqlQuery, queryParams);
        const { rows } = await pool.query(sqlQuery, queryParams);
        res.json(rows);

    } catch (error) {
        console.error('Erro ao executar a query', error);
        res.status(500).send('Erro ao executar a query');
    }
});

app.listen(PORT, () => console.log(`A correr na porta http://localhost:${PORT}`));
