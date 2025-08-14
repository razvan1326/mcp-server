#!/bin/bash

# Remote MCP Server Setup Script pentru AlmaLinux
# Complet separat de implementarea locală

echo "🚀 Setting up Remote MCP Server pentru Academiadepolitie.com"
echo "================================================"

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
   echo "❌ Please run as root (use sudo)"
   exit 1
fi

# Variables
MCP_DIR="/var/www/mcp.academiadepolitie.com"
SERVICE_NAME="remote-mcp"
NODE_VERSION="18"

echo "📦 Step 1: Installing Node.js v${NODE_VERSION}..."
# Install Node.js 18+ if not present
if ! command -v node &> /dev/null || [ $(node -v | cut -d'v' -f2 | cut -d'.' -f1) -lt ${NODE_VERSION} ]; then
    curl -fsSL https://rpm.nodesource.com/setup_${NODE_VERSION}.x | bash -
    dnf install -y nodejs
    echo "✅ Node.js installed: $(node -v)"
else
    echo "✅ Node.js already installed: $(node -v)"
fi

echo "📁 Step 2: Creating directory structure..."
mkdir -p ${MCP_DIR}
cp -r ../src ${MCP_DIR}/
cp -r ../auth ${MCP_DIR}/
cp ../package.json ${MCP_DIR}/
cp ../.env.example ${MCP_DIR}/.env

echo "📦 Step 3: Installing dependencies..."
cd ${MCP_DIR}
npm install --production

echo "🔐 Step 4: Setting up environment..."
# Generate secure secrets
JWT_SECRET=$(openssl rand -hex 32)
OAUTH_SECRET=$(openssl rand -hex 32)

# Update .env file
sed -i "s/your_jwt_secret_here/${JWT_SECRET}/" .env
sed -i "s/your_secure_secret_here/${OAUTH_SECRET}/" .env

echo "🔥 Step 5: Configuring firewall..."
# Open ports for Remote MCP
firewall-cmd --permanent --add-port=3000/tcp
firewall-cmd --reload
echo "✅ Firewall configured"

echo "⚙️ Step 6: Creating systemd service..."
cat > /etc/systemd/system/${SERVICE_NAME}.service << EOF
[Unit]
Description=Remote MCP Server for Academiadepolitie.com
After=network.target

[Service]
Type=simple
User=nobody
WorkingDirectory=${MCP_DIR}
ExecStart=/usr/bin/node src/server.js
Restart=always
RestartSec=10
StandardOutput=syslog
StandardError=syslog
SyslogIdentifier=${SERVICE_NAME}
Environment=NODE_ENV=production

[Install]
WantedBy=multi-user.target
EOF

echo "🚀 Step 7: Starting service..."
systemctl daemon-reload
systemctl enable ${SERVICE_NAME}
systemctl start ${SERVICE_NAME}

echo "📊 Step 8: Checking service status..."
sleep 2
if systemctl is-active --quiet ${SERVICE_NAME}; then
    echo "✅ Remote MCP Server is running!"
    echo ""
    echo "🎉 SETUP COMPLETE!"
    echo "==================="
    echo "📡 Server running on: http://localhost:3000"
    echo "📝 Logs: journalctl -u ${SERVICE_NAME} -f"
    echo "🔧 Config: ${MCP_DIR}/.env"
    echo ""
    echo "⚠️  NEXT STEPS:"
    echo "1. Configure nginx reverse proxy for mcp.academiadepolitie.com"
    echo "2. Setup SSL certificate with Let's Encrypt"
    echo "3. Update DNS records"
    echo "4. Test OAuth flow"
else
    echo "❌ Service failed to start. Check logs:"
    echo "journalctl -u ${SERVICE_NAME} -n 50"
    exit 1
fi