import { Pool } from "pg";

/**
 * Centralized Postgres pool.
 * Reads from environment first so you can run via Cloud SQL Proxy or override credentials locally.
 */
const pool = new Pool({
  user: process.env.PGUSER || "admin",
  password: process.env.PGPASSWORD || "Passwordbd1!",
  database: process.env.PGDATABASE || "biomap",
  host: process.env.PGHOST || "34.175.211.25",
  port: Number(process.env.PGPORT || 5432),
  ssl:
    process.env.PGSSL === "false"
      ? false
      : {
          rejectUnauthorized: false,
        },
  // Allow more time for remote/public connections
  connectionTimeoutMillis: Number(process.env.PGCONNECT_TIMEOUT || 15000),
  idleTimeoutMillis: 30000,
});

// Log pool-level errors to help diagnose ECONNRESET/handshake issues
pool.on("error", (err) => {
  console.error("Postgres pool error:", err);
});

// Log minimal connection info to help diagnose network issues (no secrets)
console.log(
  `[DB] Connecting to ${process.env.PGHOST || "34.175.211.25"}:${Number(
    process.env.PGPORT || 5432
  )} ssl=${process.env.PGSSL === "false" ? "off" : "on"}`
);

export default pool;