// api/cron/cleanup-avistamentos.js
import pool from "../../bd.js";

export default async function handler(req, res) {
  // Only allow GET requests (Vercel cron jobs use GET)
  if (req.method !== 'GET') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  // Optional: Verify this is a cron job request
  const authHeader = req.headers['authorization'];
  if (process.env.CRON_SECRET && authHeader !== `Bearer ${process.env.CRON_SECRET}`) {
    return res.status(401).json({ error: 'Unauthorized' });
  }

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
}

