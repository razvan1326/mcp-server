<?php
/**
 * OAuth Bridge pentru Remote MCP - Integrare cu sistemul PHP existent
 * Acest script conectează autentificarea PHP cu flow-ul OAuth necesar pentru Claude
 */

// Include sistemul PHP existent  
require_once '/home/adpcomilearnigs/public_html/core/init.php';

// Headers pentru API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://claude.ai');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Configurație OAuth
$OAUTH_CLIENT_ID = $_ENV['OAUTH_CLIENT_ID'] ?? 'academiadepolitie_remote_mcp';
$OAUTH_CLIENT_SECRET = $_ENV['OAUTH_CLIENT_SECRET'] ?? '';
$JWT_SECRET = $_ENV['JWT_SECRET'] ?? '';
$BASE_URL = 'https://mcp.academiadepolitie.com:8443';

/**
 * Generează JWT token pentru user
 */
function generateJWT($userData, $secret) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'iss' => 'academiadepolitie.com',
        'sub' => $userData->id,
        'userId' => $userData->id,
        'username' => $userData->username,
        'email' => $userData->email,
        'iat' => time(),
        'exp' => time() + (24 * 60 * 60) // 24 ore
    ]);
    
    $headerEncoded = base64url_encode($header);
    $payloadEncoded = base64url_encode($payload);
    
    $signature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, $secret, true);
    $signatureEncoded = base64url_encode($signature);
    
    return $headerEncoded . "." . $payloadEncoded . "." . $signatureEncoded;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Generează authorization code temporar
 */
function generateAuthCode($userId) {
    return base64_encode($userId . '_' . time() . '_' . bin2hex(random_bytes(16)));
}

/**
 * Validează authorization code
 */
function validateAuthCode($code) {
    $decoded = base64_decode($code);
    $parts = explode('_', $decoded);
    
    if (count($parts) !== 3) return false;
    
    $userId = $parts[0];
    $timestamp = (int)$parts[1];
    
    // Code valid 10 minute
    if (time() - $timestamp > 600) return false;
    
    return $userId;
}

// Router principal
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

try {
    switch ($path) {
        case '/oauth/authorize':
            handleAuthorize();
            break;
            
        case '/oauth/token':
            handleToken();
            break;
            
        case '/oauth/login':
            handleLogin();
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'endpoint_not_found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server_error', 'message' => $e->getMessage()]);
}

/**
 * Handle OAuth authorization endpoint
 */
function handleAuthorize() {
    global $BASE_URL;
    
    $clientId = $_GET['client_id'] ?? '';
    $redirectUri = $_GET['redirect_uri'] ?? '';
    $state = $_GET['state'] ?? '';
    $codeChallenge = $_GET['code_challenge'] ?? '';
    $codeChallengeMethod = $_GET['code_challenge_method'] ?? 'S256';
    
    // Validare parametri OAuth
    if (empty($clientId) || empty($redirectUri)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_request']);
        return;
    }
    
    // Check dacă user-ul este logat
    $user = new User();
    
    if ($user->isLoggedIn()) {
        // User logat - generează auth code și redirect
        $authCode = generateAuthCode($user->data()->id);
        
        // Store auth code cu metadata (în producție ar trebui în Redis/DB)
        $_SESSION['oauth_codes'][$authCode] = [
            'user_id' => $user->data()->id,
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'code_challenge' => $codeChallenge,
            'expires_at' => time() + 600
        ];
        
        // Redirect cu authorization code
        $redirectUrl = $redirectUri . '?code=' . urlencode($authCode);
        if ($state) {
            $redirectUrl .= '&state=' . urlencode($state);
        }
        
        echo json_encode([
            'redirect' => $redirectUrl,
            'message' => 'User authenticated, redirecting...'
        ]);
    } else {
        // User nu este logat - redirect la login
        $loginUrl = $BASE_URL . '/oauth/login?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => $codeChallengeMethod
        ]);
        
        echo json_encode([
            'login_required' => true,
            'login_url' => $loginUrl
        ]);
    }
}

/**
 * Handle login page (simplă pentru test)
 */
function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Process login
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Username și parola sunt obligatorii']);
            return;
        }
        
        // Încearcă login
        $user = new User();
        $loginResult = $user->login($username, $password, false);
        
        if ($loginResult) {
            // Login successful - redirect back to authorization
            $authUrl = '/oauth/authorize?' . http_build_query($_GET);
            echo json_encode([
                'success' => true,
                'redirect' => $authUrl,
                'message' => 'Login successful'
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Username sau parolă incorectă']);
        }
    } else {
        // Show login form (HTML simplu pentru test)
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Login - Academia de Politie MCP</title>
            <meta charset="utf-8">
            <style>
                body { font-family: Arial, sans-serif; max-width: 400px; margin: 100px auto; padding: 20px; }
                .form-group { margin-bottom: 15px; }
                label { display: block; margin-bottom: 5px; font-weight: bold; }
                input[type="text"], input[type="password"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
                button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
                button:hover { background: #005a87; }
                .error { color: red; margin-top: 10px; }
                .header { text-align: center; margin-bottom: 30px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>Academia de Politie</h2>
                <p>Autentificare pentru accesul la MCP</p>
            </div>
            
            <form id="loginForm">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Parola:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit">Login</button>
                
                <div id="error" class="error" style="display:none;"></div>
            </form>
            
            <script>
                document.getElementById('loginForm').onsubmit = async (e) => {
                    e.preventDefault();
                    
                    const formData = new FormData(e.target);
                    const urlParams = new URLSearchParams(window.location.search);
                    
                    // Add OAuth parameters
                    for (let [key, value] of urlParams) {
                        formData.append(key, value);
                    }
                    
                    try {
                        const response = await fetch('/oauth/login', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            window.location.href = result.redirect;
                        } else {
                            document.getElementById('error').style.display = 'block';
                            document.getElementById('error').textContent = result.error || 'Eroare la login';
                        }
                    } catch (err) {
                        document.getElementById('error').style.display = 'block';
                        document.getElementById('error').textContent = 'Eroare de conexiune';
                    }
                };
            </script>
        </body>
        </html>
        <?php
    }
}

/**
 * Handle token exchange endpoint
 */
function handleToken() {
    global $OAUTH_CLIENT_SECRET, $JWT_SECRET;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'method_not_allowed']);
        return;
    }
    
    $grantType = $_POST['grant_type'] ?? '';
    $code = $_POST['code'] ?? '';
    $clientId = $_POST['client_id'] ?? '';
    $codeVerifier = $_POST['code_verifier'] ?? '';
    
    if ($grantType !== 'authorization_code') {
        http_response_code(400);
        echo json_encode(['error' => 'unsupported_grant_type']);
        return;
    }
    
    if (empty($code) || empty($clientId)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_request']);
        return;
    }
    
    // Validează authorization code
    $userId = validateAuthCode($code);
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_grant']);
        return;
    }
    
    // Verifică dacă code-ul există în sesiune
    if (!isset($_SESSION['oauth_codes'][$code])) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_grant']);
        return;
    }
    
    $codeData = $_SESSION['oauth_codes'][$code];
    
    // Validează client_id și code challenge (PKCE)
    if ($codeData['client_id'] !== $clientId) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_client']);
        return;
    }
    
    // Verifică PKCE challenge
    if (!empty($codeData['code_challenge'])) {
        $expectedChallenge = base64url_encode(hash('sha256', $codeVerifier, true));
        if ($expectedChallenge !== $codeData['code_challenge']) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid_grant']);
            return;
        }
    }
    
    // Get user data
    $user = new User($userId);
    if (!$user->data()) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_user']);
        return;
    }
    
    // Generate access token (JWT)
    $accessToken = generateJWT($user->data(), $JWT_SECRET);
    
    // Clean up used code
    unset($_SESSION['oauth_codes'][$code]);
    
    // Return token response
    echo json_encode([
        'access_token' => $accessToken,
        'token_type' => 'Bearer',
        'expires_in' => 86400, // 24 ore
        'scope' => 'mcp_access'
    ]);
}