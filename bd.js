import { Pool } from "pg";

/**
 * Centralized Postgres pool.
 * Optimized for both traditional servers and serverless environments (Vercel).
 * In serverless, connections are reused across warm invocations.
 */
const isVercel = process.env.VERCEL === "1" || process.env.VERCEL_ENV;

// Serverless-optimized pool configuration
const poolConfig = {
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
  // Connection timeout
  connectionTimeoutMillis: Number(process.env.PGCONNECT_TIMEOUT || 15000),
  // For serverless: shorter idle timeout, fewer max connections
  idleTimeoutMillis: isVercel ? 10000 : 30000,
  max: isVercel ? 2 : 20, // Serverless: 2 connections per function instance (for concurrent requests)
  min: 0, // Don't keep idle connections in serverless
};

const pool = new Pool(poolConfig);

// Log pool-level errors to help diagnose ECONNRESET/handshake issues
pool.on("error", (err) => {
  console.error("Postgres pool error:", err);
});

// Log minimal connection info (only on first import, not on every invocation)
if (!global.__dbPoolInitialized) {
  console.log(
    `[DB] ${isVercel ? "[Vercel] " : ""}Connecting to ${process.env.PGHOST || "34.175.211.25"}:${Number(
      process.env.PGPORT || 5432
    )} ssl=${process.env.PGSSL === "false" ? "off" : "on"}`
  );
  global.__dbPoolInitialized = true;
}

// Graceful shutdown for serverless (though Vercel handles this automatically)
if (!isVercel && typeof process !== "undefined") {
  process.on("SIGINT", async () => {
    await pool.end();
    process.exit(0);
  });
}

export default pool;