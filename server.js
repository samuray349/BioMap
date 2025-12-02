
import express from "express"
import path2 from "path";
import { fileURLToPath } from "url"
import { appendFile } from "fs";
const __filename = fileURLToPath(import.meta.url);
const __dirname = path2.dirname(__filename);
const app = express();
const PORT = process.env.PORT || 3000;

import { Pool } from "pg";


// The connection name format: /cloudsql/project-id:region:instance-name
const connectionName = "affable-ring-474412-q1:europe-southwest1:instancia-bio-map"; 

// ...
const PUBLIC_IP = "34.175.211.25"; 

const pool = new Pool({
  user: "admin",
  password: "Passwordbd1!",
  database: "biomap",
  // --- Use ONLY the Public IP address here ---
  host: PUBLIC_IP, 
  port: 5432,           // Standard PostgreSQL port
  ssl: {
    rejectUnauthorized: false // Accept self-signed certs (common for Cloud SQL public IP)
  }
});
// ...

app.use(express.static(path2.join(__dirname, "public")));

app.get("/", (req, res) => {
  res.sendFile(path2.join(__dirname, "public", "index.html"));
});
app.get('/users', async (req, res) => {
    try {
      const { rows } = await pool.query('SELECT utilizador_id, nome_utilizador, email FROM utilizador ORDER BY utilizador_id');
      res.json(rows);
    } catch (error) {
      console.error('Error executing query', error);
      res.status(500).send('Database Error');
    }
  });
app.get('/animais', async (req, res) => {
    try {
      const { rows } = await pool.query('SELECT * FROM animal');
      res.json(rows);
    } catch (error) {
      console.error('Error executing query', error);
      res.status(500).send('Database Error');
    }
  });

app.listen(PORT, () => console.log(`A correr na porta http://localhost:${PORT}`));
