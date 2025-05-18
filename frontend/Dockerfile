# Base image
FROM node:20-alpine

# Set working directory
WORKDIR /app

# Install deps
COPY package.json package-lock.json ./
RUN npm install

# Copy the rest of the code
COPY . .

# Expose the port Vite uses
EXPOSE 5173

# Start Vite dev server
CMD ["npm", "run", "dev", "--", "--host"]
