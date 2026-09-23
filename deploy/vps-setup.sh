#!/bin/bash
set -e

echo "=========================================="
echo "🛠️ Initializing VPS Setup for Keuangan Gereja"
echo "=========================================="

# 1. Update packages
sudo apt update && sudo apt upgrade -y

# 2. Install prerequisites
sudo apt install -y curl wget git ufw apt-transport-https ca-certificates gnupg lsb-release

# 3. Install Docker CE & Docker Compose plugin if not installed
if ! command -v docker &> /dev/null; then
    echo "🐳 Installing Docker CE..."
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $USER
    rm get-docker.sh
    echo "✅ Docker installed successfully."
fi

# 4. Create deployment directories
DEPLOY_DIR="/opt/stacks/keuangan-gereja"
echo "📁 Creating stack directories at $DEPLOY_DIR..."
sudo mkdir -p "$DEPLOY_DIR/storage/logs" "$DEPLOY_DIR/backups"
sudo chown -R $USER:$USER "$DEPLOY_DIR"
sudo chmod -R 775 "$DEPLOY_DIR"

# 5. Configure Firewall (UFW)
echo "🛡️ Configuring Firewall (UFW)..."
sudo ufw allow 22/tcp   # SSH
sudo ufw allow 80/tcp   # HTTP (Cloudflare Proxy)
sudo ufw allow 443/tcp  # HTTPS
sudo ufw --force enable

echo "=========================================="
echo "✅ VPS Setup completed!"
echo "Next steps:"
echo "1. Place your production .env at: $DEPLOY_DIR/.env"
echo "2. Place your docker-compose.prod.yml (renamed to docker-compose.yml) at: $DEPLOY_DIR/docker-compose.yml"
echo "3. Add GitHub Secrets in your repo: VPS_HOST, VPS_USERNAME, VPS_SSH_KEY, VPS_SSH_PORT, VPS_DEPLOY_PATH"
echo "=========================================="
