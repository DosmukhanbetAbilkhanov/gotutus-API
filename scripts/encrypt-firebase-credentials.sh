#!/bin/bash

# Encrypt Firebase Credentials for GitHub Deployment
# This script encrypts firebase-credentials.json so it can be safely committed to git

set -e  # Exit on error

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
CREDENTIALS_FILE="$PROJECT_ROOT/storage/app/firebase-credentials.json"
ENCRYPTED_FILE="$PROJECT_ROOT/storage/app/firebase-credentials.json.enc"

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║   Encrypt Firebase Credentials for GitHub Deployment    ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

# Check if credentials file exists
if [ ! -f "$CREDENTIALS_FILE" ]; then
    echo "❌ Error: firebase-credentials.json not found at:"
    echo "   $CREDENTIALS_FILE"
    echo ""
    echo "Please ensure the file exists before running this script."
    exit 1
fi

echo "✅ Found credentials file: $CREDENTIALS_FILE"
echo ""

# Check if password is provided as argument
if [ -z "$1" ]; then
    echo "📝 Generating a secure encryption password..."
    PASSWORD=$(openssl rand -base64 32)
    echo ""
    echo "╔═══════════════════════════════════════════════════════════╗"
    echo "║               🔑 SAVE THIS PASSWORD!                     ║"
    echo "╚═══════════════════════════════════════════════════════════╝"
    echo ""
    echo "Password: $PASSWORD"
    echo ""
    echo "⚠️  You MUST save this password! You'll need it to:"
    echo "   1. Add to Forge environment variables (.env)"
    echo "   2. Re-encrypt if you update credentials"
    echo ""
    read -p "Press Enter after you've saved the password..."
    echo ""
else
    PASSWORD="$1"
    echo "✅ Using provided password"
    echo ""
fi

# Encrypt the file
echo "🔐 Encrypting credentials..."
openssl enc -aes-256-cbc -salt \
  -in "$CREDENTIALS_FILE" \
  -out "$ENCRYPTED_FILE" \
  -pass pass:"$PASSWORD"

if [ $? -eq 0 ]; then
    echo "✅ Credentials encrypted successfully!"
    echo ""
    echo "Encrypted file: $ENCRYPTED_FILE"
    echo ""

    # Check file sizes
    ORIGINAL_SIZE=$(ls -lh "$CREDENTIALS_FILE" | awk '{print $5}')
    ENCRYPTED_SIZE=$(ls -lh "$ENCRYPTED_FILE" | awk '{print $5}')
    echo "Original size:  $ORIGINAL_SIZE"
    echo "Encrypted size: $ENCRYPTED_SIZE"
    echo ""

    echo "╔═══════════════════════════════════════════════════════════╗"
    echo "║                    📋 NEXT STEPS                         ║"
    echo "╚═══════════════════════════════════════════════════════════╝"
    echo ""
    echo "1. Add to .gitignore (if not already):"
    echo "   echo 'storage/app/firebase-credentials.json' >> .gitignore"
    echo ""
    echo "2. Commit the encrypted file:"
    echo "   git add storage/app/firebase-credentials.json.enc"
    echo "   git add .gitignore"
    echo "   git commit -m 'Add encrypted Firebase credentials'"
    echo "   git push origin main"
    echo ""
    echo "3. Add password to Forge environment:"
    echo "   Go to: Forge Dashboard → Sites → tanys.app → Environment"
    echo "   Add: FIREBASE_CREDENTIALS_PASSWORD=$PASSWORD"
    echo ""
    echo "4. Update Forge deployment script to decrypt on deployment"
    echo "   See: .ai/firebase-credentials-github-deployment.md"
    echo ""
else
    echo "❌ Encryption failed!"
    exit 1
fi
