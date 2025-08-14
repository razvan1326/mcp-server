# Comenzi pentru setup pe server

## 1. În directorul /opt/mcp-server/ pe server:

```bash
# Instalează dependințele Node.js (IMPORTANT - lipseau!)
npm install

# Verifică că toate dependințele sunt instalate
npm list

# Setează permisiuni corecte
chmod 755 oauth-bridge.php
chown -R nobody:nobody .

# Restart service
systemctl restart remote-mcp
systemctl status remote-mcp --no-pager
```

## 2. Test că funcționează:

```bash
# Test Node.js direct
curl -s "http://127.0.0.1:3000/health"

# Test prin Apache
curl -s "https://127.0.0.1:8443/health" -k
```

## Problema principală:
Serverul Node.js nu pornește pentru că **lipsesc dependințele npm**! 

Trebuie să rulezi `npm install` în `/opt/mcp-server/` pe VPS.