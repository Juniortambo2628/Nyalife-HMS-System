#!/bin/bash
# Setup SSH key for GitHub Actions on production server
# Run this ONCE on the production server after adding the public key

PUB_KEY="ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIH/kBoQ3TpTzyQ82yUNKMl4E2AXUKe3pdpr0b55p5LL5 github-actions-nyalife-hms"

echo "🔐 Setting up GitHub Actions SSH key..."

# Create .ssh directory if it doesn't exist
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Add public key to authorized_keys if not already present
if ! grep -q "github-actions-nyalife-hms" ~/.ssh/authorized_keys 2>/dev/null; then
    echo "$PUB_KEY" >> ~/.ssh/authorized_keys
    echo "✅ Added GitHub Actions public key to authorized_keys"
else
    echo "ℹ️ GitHub Actions key already in authorized_keys"
fi

# Set correct permissions
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh

echo "✅ GitHub Actions SSH key setup complete!"
echo ""
echo "🔒 Verify key is working:"
echo "   ssh -T git@github.com"
echo ""
echo "📋 Add the PRIVATE key to GitHub Actions secrets:"
echo "   Settings → Secrets → Actions → SSH_PRIVATE_KEY"
echo ""
cat ~/.ssh/id_ed25519 2>/dev/null || echo "   (Run this on server to show private key if needed)"