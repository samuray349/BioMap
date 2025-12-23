// server.js
import express from "express";
import path2 from "path";
import { fileURLToPath } from "url";
import fs from "fs/promises";
import crypto from "crypto";
import nodemailer from "nodemailer";
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
   EMAIL CONFIGURATION
===================== */
// Configure nodemailer transporter
// Using Gmail SMTP - you may need to use an App Password instead of regular password
const emailTransporter = nodemailer.createTransport({
  service: 'gmail',
  auth: {
    user: 'pedrovan14@gmail.com',
    pass: process.env.EMAIL_PASSWORD || ''
  }
});

// Verify email configuration (optional, for testing)
// Don't block deployment if email verification fails
emailTransporter.verify((error, success) => {
  if (error) {
    console.warn('Email configuration warning:', error.message);
    console.warn('Email sending may not work until EMAIL_PASSWORD is configured');
  } else {
    console.log('Email server is ready to send messages');
  }
});


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
        u.estado_id,
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
  // imagem_url is optional - can be updated later
  
  // Validate ameacas (threats) - must be exactly 5 non-empty threats
  if (!Array.isArray(ameacas)) {
    errors.push('Ameaças devem ser fornecidas como um array.');
  } else {
    const nonEmptyThreats = ameacas
      .map((t) => (t || '').trim())
      .filter((t) => t.length > 0);
    if (nonEmptyThreats.length !== 5) {
      errors.push(`Deve fornecer exatamente 5 ameaças. Fornecidas: ${nonEmptyThreats.length}.`);
    }
  }
  
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

    // Check for duplicate animal name (case-insensitive, trimmed)
    const duplicateCheck = await client.query(
      'SELECT animal_id FROM animal WHERE LOWER(TRIM(nome_comum)) = LOWER(TRIM($1)) LIMIT 1',
      [nome_comum.trim()]
    );
    if (duplicateCheck.rowCount > 0) {
      await client.query('ROLLBACK');
      return res.status(400).json({ error: `Já existe um animal com o nome "${nome_comum.trim()}". Por favor, escolha um nome diferente.` });
    }

    // Use TRIM in the query to handle any trailing spaces in database
    const familia = await client.query(
      'SELECT familia_id FROM familia WHERE TRIM(nome_familia) = TRIM($1) LIMIT 1',
      [familia_nome.trim()]
    );
    if (familia.rowCount === 0) {
      await client.query('ROLLBACK');
      return res.status(400).json({ error: `Família "${familia_nome.trim()}" não encontrada na base de dados. Por favor, selecione uma família válida.` });
    }

    const dieta = await client.query(
      'SELECT dieta_id FROM dieta WHERE TRIM(nome_dieta) = TRIM($1) LIMIT 1',
      [dieta_nome.trim()]
    );
    if (dieta.rowCount === 0) {
      await client.query('ROLLBACK');
      return res.status(400).json({ error: `Dieta "${dieta_nome.trim()}" não encontrada na base de dados. Por favor, selecione uma dieta válida.` });
    }

    // Use TRIM in the query to handle trailing spaces in database
    const estado = await client.query(
      'SELECT estado_id FROM estado_conservacao WHERE TRIM(nome_estado) = TRIM($1) LIMIT 1',
      [estado_nome.trim()]
    );
    if (estado.rowCount === 0) {
      await client.query('ROLLBACK');
      return res.status(400).json({ 
        error: `Estado de conservação "${estado_nome.trim()}" não encontrado na base de dados. Por favor, selecione um estado válido.` 
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
        finalImageUrl,
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
      url_imagem: finalImageUrl
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
    ameacas = [],
    imagem_url
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
  
  // Validate ameacas (threats) - must be exactly 5 non-empty threats
  if (!Array.isArray(ameacas)) {
    errors.push('Ameaças devem ser fornecidas como um array.');
  } else {
    const nonEmptyThreats = ameacas
      .map((t) => (t || '').trim())
      .filter((t) => t.length > 0);
    if (nonEmptyThreats.length !== 5) {
      errors.push(`Deve fornecer exatamente 5 ameaças. Fornecidas: ${nonEmptyThreats.length}.`);
    }
  }
  
  if (errors.length > 0) {
    return res.status(400).json({ error: errors.join(' ') });
  }

  const normalizedPopulation =
    typeof populacao_estimada === 'number'
      ? populacao_estimada
      : Number(String(populacao_estimada || '').replace(/[^\d]/g, '')) || null;

  // Validate population is within PostgreSQL integer range (-2,147,483,648 to 2,147,483,647)
  if (normalizedPopulation !== null && (normalizedPopulation > 2147483647 || normalizedPopulation < -2147483648)) {
    return res.status(400).json({ 
      error: `População estimada "${populacao_estimada}" está fora do intervalo permitido. O valor deve estar entre -2,147,483,648 e 2,147,483,647.` 
    });
  }

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
      return res.status(400).json({ error: `Família "${familia_nome.trim()}" não encontrada na base de dados. Por favor, selecione uma família válida.` });
    }

    const dieta = await client.query(
      'SELECT dieta_id FROM dieta WHERE TRIM(nome_dieta) = TRIM($1) LIMIT 1',
      [dieta_nome.trim()]
    );
    if (dieta.rowCount === 0) {
      await client.query('ROLLBACK');
      return res.status(400).json({ error: `Dieta "${dieta_nome.trim()}" não encontrada na base de dados. Por favor, selecione uma dieta válida.` });
    }

    // Use TRIM in the query to handle trailing spaces in database
    const estado = await client.query(
      'SELECT estado_id FROM estado_conservacao WHERE TRIM(nome_estado) = TRIM($1) LIMIT 1',
      [estado_nome.trim()]
    );
    if (estado.rowCount === 0) {
      await client.query('ROLLBACK');
      return res.status(400).json({ 
        error: `Estado de conservação "${estado_nome.trim()}" não encontrado na base de dados. Por favor, selecione um estado válido.` 
      });
    }

    // Update animal (including image URL if provided)
    if (imagem_url) {
      await client.query(
        `UPDATE animal SET
          nome_comum = $1,
          nome_cientifico = $2,
          descricao = $3,
          facto_interessante = $4,
          populacao_estimada = $5,
          dieta_id = $6,
          familia_id = $7,
          estado_id = $8,
          url_imagem = $9
        WHERE animal_id = $10`,
        [
          nome_comum,
          nome_cientifico,
          descricao,
          facto_interessante || '',
          normalizedPopulation,
          dieta.rows[0].dieta_id,
          familia.rows[0].familia_id,
          estado.rows[0].estado_id,
          imagem_url,
          id
        ]
      );
    } else {
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
    }

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

      // Delete related records first (in order of foreign key dependencies)
      // 1. Delete avistamentos (sightings) that reference this animal
      await client.query('DELETE FROM avistamento WHERE animal_id = $1', [id]);
      
      // 2. Delete animal_ameaca relationships
      await client.query('DELETE FROM animal_ameaca WHERE animal_id = $1', [id]);
      
      // 3. Finally, delete the animal itself
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
   PASSWORD RESET
   POST /api/forgot-password
   POST /api/reset-password
===================== */

// Create password_reset_tokens table if it doesn't exist
async function ensurePasswordResetTable() {
  try {
    await pool.query(`
      CREATE TABLE IF NOT EXISTS password_reset_tokens (
        token_id SERIAL PRIMARY KEY,
        utilizador_id INTEGER NOT NULL REFERENCES utilizador(utilizador_id) ON DELETE CASCADE,
        token VARCHAR(255) NOT NULL UNIQUE,
        expires_at TIMESTAMP NOT NULL,
        used BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT NOW()
      )
    `);
    console.log('Password reset tokens table ensured');
  } catch (error) {
    console.error('Error ensuring password reset table:', error);
  }
}

// Initialize table on server start
ensurePasswordResetTable();

// POST /api/forgot-password - Request password reset
app.post('/api/forgot-password', async (req, res) => {
  try {
    const { email } = req.body;

    if (!email || !email.trim()) {
      return res.status(400).json({ error: 'Email é obrigatório.' });
    }

    // Basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.trim())) {
      return res.status(400).json({ error: 'Email inválido.' });
    }

    // Check if user exists
    const userResult = await pool.query(
      'SELECT utilizador_id, nome_utilizador, email FROM utilizador WHERE email = $1',
      [email.trim()]
    );

    // Always return success message (security best practice - don't reveal if email exists)
    if (userResult.rowCount === 0) {
      return res.status(200).json({ 
        message: 'Se o email existir na nossa base de dados, receberá instruções para redefinir a password.' 
      });
    }

    const user = userResult.rows[0];

    // Generate secure token
    const resetToken = crypto.randomBytes(32).toString('hex');
    const expiresAt = new Date();
    expiresAt.setHours(expiresAt.getHours() + 1); // Token expires in 1 hour

    // Invalidate any existing tokens for this user
    await pool.query(
      'UPDATE password_reset_tokens SET used = TRUE WHERE utilizador_id = $1 AND used = FALSE',
      [user.utilizador_id]
    );

    // Store token in database
    await pool.query(
      'INSERT INTO password_reset_tokens (utilizador_id, token, expires_at) VALUES ($1, $2, $3)',
      [user.utilizador_id, resetToken, expiresAt]
    );

    // Create reset URL - use Hostinger frontend domain
    const frontendUrl = process.env.FRONTEND_URL || 'https://lucped.antrob.eu';
    const resetUrl = `${frontendUrl}/public/repor_password.php?token=${resetToken}`;

    // Send email
    const mailOptions = {
      from: 'pedrovan14@gmail.com',
      to: user.email,
      subject: 'Redefinir Password - BioMap',
      html: `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
          <h2 style="color: #1A8F4A;">Redefinir Password</h2>
          <p>Olá ${user.nome_utilizador},</p>
          <p>Recebemos um pedido para redefinir a password da sua conta BioMap.</p>
          <p>Clique no link abaixo para criar uma nova password:</p>
          <p style="margin: 20px 0;">
            <a href="${resetUrl}" style="background-color: #1A8F4A; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
              Redefinir Password
            </a>
          </p>
          <p>Ou copie e cole este link no seu navegador:</p>
          <p style="word-break: break-all; color: #666;">${resetUrl}</p>
          <p style="color: #999; font-size: 12px; margin-top: 30px;">
            Este link expira em 1 hora. Se não solicitou esta alteração, ignore este email.
          </p>
          <p style="color: #999; font-size: 12px;">
            Atenciosamente,<br>Equipa BioMap
          </p>
        </div>
      `,
      text: `
        Redefinir Password
        
        Olá ${user.nome_utilizador},
        
        Recebemos um pedido para redefinir a password da sua conta BioMap.
        
        Clique no link abaixo para criar uma nova password:
        ${resetUrl}
        
        Este link expira em 1 hora. Se não solicitou esta alteração, ignore este email.
        
        Atenciosamente,
        Equipa BioMap
      `
    };

    try {
      await emailTransporter.sendMail(mailOptions);
      return res.status(200).json({ 
        message: 'Se o email existir na nossa base de dados, receberá instruções para redefinir a password.' 
      });
    } catch (emailError) {
      console.error('Error sending email:', emailError);
      // Still return success to user (security best practice)
      return res.status(200).json({ 
        message: 'Se o email existir na nossa base de dados, receberá instruções para redefinir a password.' 
      });
    }
  } catch (error) {
    console.error('Erro ao processar pedido de redefinição de password:', error);
    return res.status(500).json({ error: 'Erro ao processar pedido. Tente novamente mais tarde.' });
  }
});

// POST /api/reset-password - Reset password with token
app.post('/api/reset-password', async (req, res) => {
  try {
    const { token, password } = req.body;

    if (!token || !token.trim()) {
      return res.status(400).json({ error: 'Token é obrigatório.' });
    }

    if (!password) {
      return res.status(400).json({ error: 'Password é obrigatória.' });
    }

    // Validate password strength
    const pwdPolicy = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
    if (!pwdPolicy.test(password)) {
      return res.status(400).json({ 
        error: 'A password deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma letra minúscula e um número.' 
      });
    }

    // Find valid token
    const tokenResult = await pool.query(
      `SELECT prt.token_id, prt.utilizador_id, prt.expires_at, prt.used, u.email
       FROM password_reset_tokens prt
       JOIN utilizador u ON prt.utilizador_id = u.utilizador_id
       WHERE prt.token = $1`,
      [token.trim()]
    );

    if (tokenResult.rowCount === 0) {
      return res.status(400).json({ error: 'Token inválido ou expirado.' });
    }

    const tokenData = tokenResult.rows[0];

    // Check if token is already used
    if (tokenData.used) {
      return res.status(400).json({ error: 'Este token já foi utilizado.' });
    }

    // Check if token is expired
    if (new Date() > new Date(tokenData.expires_at)) {
      return res.status(400).json({ error: 'Token expirado. Solicite um novo link de redefinição.' });
    }

    // Hash new password
    const passwordHash = crypto.createHash('sha256').update(password).digest('hex');

    // Update password
    await pool.query(
      'UPDATE utilizador SET password_hash = $1 WHERE utilizador_id = $2',
      [passwordHash, tokenData.utilizador_id]
    );

    // Mark token as used
    await pool.query(
      'UPDATE password_reset_tokens SET used = TRUE WHERE token_id = $1',
      [tokenData.token_id]
    );

    return res.status(200).json({ 
      message: 'Password redefinida com sucesso.' 
    });
  } catch (error) {
    console.error('Erro ao redefinir password:', error);
    return res.status(500).json({ error: 'Erro ao redefinir password. Tente novamente mais tarde.' });
  }
});

/* =====================
   CRON JOBS (Scheduled Tasks)
   GET /cron/cleanup-tokens
   GET /cron/cleanup-avistamentos
===================== */

// Cleanup expired password reset tokens
app.get('/cron/cleanup-tokens', async (req, res) => {
  try {
    // Delete expired password reset tokens
    const result = await pool.query(
      'DELETE FROM password_reset_tokens WHERE expires_at < NOW()'
    );

    console.log(`Cleaned up ${result.rowCount} expired password reset tokens`);

    return res.status(200).json({
      success: true,
      deletedCount: result.rowCount,
      message: `Deleted ${result.rowCount} expired tokens`,
      timestamp: new Date().toISOString()
    });
  } catch (error) {
    console.error('Error cleaning up expired tokens:', error);
    return res.status(500).json({
      error: 'Failed to cleanup tokens',
      message: error.message
    });
  }
});

// Cleanup avistamentos older than 2 days
app.get('/cron/cleanup-avistamentos', async (req, res) => {
  try {
    // Delete avistamentos older than 2 days
    // Using data_avistamento field (the creation date)
    const result = await pool.query(
      `DELETE FROM avistamento 
       WHERE data_avistamento < NOW() - INTERVAL '2 days'`
    );

    console.log(`Cleaned up ${result.rowCount} avistamentos older than 2 days`);

    return res.status(200).json({
      success: true,
      deletedCount: result.rowCount,
      message: `Deleted ${result.rowCount} avistamentos older than 2 days`,
      timestamp: new Date().toISOString()
    });
  } catch (error) {
    console.error('Error cleaning up avistamentos:', error);
    return res.status(500).json({
      error: 'Failed to cleanup avistamentos',
      message: error.message
    });
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
