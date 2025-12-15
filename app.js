import express from "express";
import cors from "cors";
import crypto from "crypto";
import pool from "./bd.js";

const app = express();

/* =====================
   Middleware
===================== */
app.use(cors({ origin: "*" }));
app.use(express.json({ limit: "10mb" }));
app.use(express.urlencoded({ extended: true }));

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

    let sql = `
      SELECT 
        u.utilizador_id,
        u.nome_utilizador,
        u.email,
        e.nome_estado,
        e.hex_cor AS estado_cor,
        f.nome_funcao AS estatuto
      FROM utilizador u
      JOIN estado e ON u.estado_id = e.estado_id
      JOIN funcao f ON u.funcao_id = f.funcao_id
      WHERE 1=1
    `;

    const params = [];
    let i = 1;

    if (search) {
      sql += ` AND (u.nome_utilizador ILIKE $${i} OR u.email ILIKE $${i + 1})`;
      params.push(`%${search}%`, `%${search}%`);
      i += 2;
    }

    if (estados) {
      sql += ` AND e.nome_estado = ANY($${i})`;
      params.push(estados.split(","));
      i++;
    }

    if (estatutos) {
      sql += ` AND f.nome_funcao = ANY($${i})`;
      params.push(estatutos.split(","));
      i++;
    }

    sql += ` ORDER BY u.utilizador_id`;

    const { rows } = await pool.query(sql, params);
    res.json(rows);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Erro ao buscar utilizadores" });
  }
});

app.get("/users/:id", async (req, res) => {
  try {
    const { id } = req.params;
    if (!/^\d+$/.test(id)) {
      return res.status(400).json({ error: "ID inválido" });
    }

    const { rows } = await pool.query(
      `
      SELECT utilizador_id, nome_utilizador, email, funcao_id, estado_id
      FROM utilizador
      WHERE utilizador_id = $1
      `,
      [id]
    );

    if (!rows.length) {
      return res.status(404).json({ error: "Utilizador não encontrado" });
    }

    res.json(rows[0]);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Erro ao buscar utilizador" });
  }
});

/* =====================
   AUTH
===================== */
app.post("/api/signup", async (req, res) => {
  try {
    const { name, email, password } = req.body;

    const hash = crypto.createHash("sha256").update(password).digest("hex");

    const { rows } = await pool.query(
      `
      INSERT INTO utilizador (
        nome_utilizador,
        email,
        password_hash,
        funcao_id,
        estado_id,
        data_criacao
      )
      VALUES ($1,$2,$3,2,1,NOW())
      RETURNING utilizador_id
      `,
      [name, email, hash]
    );

    res.status(201).json({
      utilizador_id: rows[0].utilizador_id
    });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Erro ao criar utilizador" });
  }
});

app.post("/api/login", async (req, res) => {
  try {
    const { email, password } = req.body;
    const hash = crypto.createHash("sha256").update(password).digest("hex");

    const { rows } = await pool.query(
      "SELECT * FROM utilizador WHERE email = $1 LIMIT 1",
      [email]
    );

    if (!rows.length || rows[0].password_hash !== hash) {
      return res.status(401).json({ error: "Credenciais inválidas" });
    }

    res.json({
      id: rows[0].utilizador_id,
      name: rows[0].nome_utilizador,
      funcao_id: rows[0].funcao_id
    });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Erro no login" });
  }
});

/* =====================
   EXPORT
===================== */
export default app;
