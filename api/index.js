// api/index.js - Vercel serverless function handler
import app from "../server.js";

// Export the Express app as the default handler for Vercel
// Vercel will automatically pass (req, res) to the app
export default app;
