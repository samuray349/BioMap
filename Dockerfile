FROM node:20-alpine AS base

# Set working directory
WORKDIR /app

# Install only production dependencies
COPY package.json package-lock.json* ./
RUN npm ci --only=production || npm ci --omit=dev

# Copy application source
COPY . .

# Expose the port used by Express
EXPOSE 3000

# Set NODE_ENV for production
ENV NODE_ENV=production

# Start the server
CMD ["npm", "start"]


