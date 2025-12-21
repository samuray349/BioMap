// api/cron/cleanup-tokens.js
import pool from "../../bd.js";

export default async function handler(req, res) {
  // Only allow GET requests (Vercel cron jobs use GET)
  if (req.method !== 'GET') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  // Optional: Verify this is a cron job request (Vercel adds a special header)
  // You can add CRON_SECRET environment variable for extra security
  const authHeader = req.headers['authorization'];
  if (process.env.CRON_SECRET && authHeader !== `Bearer ${process.env.CRON_SECRET}`) {
    return res.status(401).json({ error: 'Unauthorized' });
  }

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
}

