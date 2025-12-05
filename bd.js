import { Pool } from "pg";

const PUBLIC_IP = "34.175.211.25"; //IP Público da instância do Cloud SQL

const pool = new Pool({
  user: "admin",
  password: "Passwordbd1!",
  database: "biomap",
  host: PUBLIC_IP, 
  port: 5432,
  ssl: {
    rejectUnauthorized: false 
  }
});

export default pool;