<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test API d'Authentification</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .tab-button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .tab-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .tab-button.active {
            background: white;
            color: #667eea;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .tab-content {
            display: none;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .response {
            margin-top: 20px;
            padding: 15px;
            border-radius: 12px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }

        .response.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .response.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .response.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .token-display {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-top: 20px;
            border: 1px solid #e1e5e9;
        }

        .token-display h4 {
            margin-bottom: 10px;
            color: #333;
        }

        .token-text {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            word-break: break-all;
            background: white;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e1e5e9;
        }

        .card h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .profile-info {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-top: 20px;
            border: 1px solid #e1e5e9;
        }

        .profile-info h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.3rem;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }

        .profile-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background: white;
            border-radius: 10px;
            border: 1px solid #e1e5e9;
            transition: all 0.3s ease;
        }

        .profile-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-label {
            font-weight: 600;
            color: #495057;
            min-width: 120px;
            margin-right: 10px;
        }

        .profile-value {
            color: #333;
            font-weight: 500;
            flex: 1;
        }

        .profile-value.verified {
            color: #28a745;
        }

        .profile-value.not-verified {
            color: #dc3545;
        }

        .profile-value.status-active {
            color: #28a745;
            font-weight: 600;
        }

        .profile-value.status-inactive {
            color: #ffc107;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .tabs {
                flex-direction: column;
                align-items: center;
            }

            .tab-button {
                width: 200px;
            }

            .profile-grid {
                grid-template-columns: 1fr;
            }

            .profile-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .profile-label {
                margin-bottom: 5px;
                min-width: auto;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Test API d'Authentification</h1>
            <p>Interface de test pour vos fonctions Laravel</p>
        </div>

        <div class="tabs">
            <button class="tab-button active" onclick="showTab('register')">📝 Inscription</button>
            <button class="tab-button" onclick="showTab('login')">🔑 Connexion</button>
            <button class="tab-button" onclick="showTab('verify')">✅ Vérification</button>
            <button class="tab-button" onclick="showTab('profile')">👤 Profil</button>
            <button class="tab-button" onclick="showTab('users')">👥 Utilisateurs</button>
            <button class="tab-button" onclick="showTab('reset')">🔄 Reset MDP</button>
            <button class="tab-button" onclick="showTab('change')">🔒 Changer MDP</button>
        </div>

        <!-- Inscription -->
        <div id="register" class="tab-content active">
            <h2>📝 Inscription d'un nouvel utilisateur</h2>
            <form id="registerForm">
                <div class="form-group">
                    <label for="reg_name">Nom complet</label>
                    <input type="text" id="reg_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="reg_email">Email</label>
                    <input type="email" id="reg_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="reg_telephone">Téléphone</label>
                    <input type="text" id="reg_telephone" name="telephone" required>
                </div>
                <div class="form-group">
                    <label for="reg_password">Mot de passe</label>
                    <input type="password" id="reg_password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="reg_role">Rôle</label>
                    <select id="reg_role" name="role">
                        <option value="operateur_entreprise">Opérateur Entreprise</option>
                        <option value="consultant_entreprise">Consultant Entreprise</option>
                        <option value="admin_entreprise">Admin Entreprise</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn">S'inscrire</button>
            </form>
            <div id="registerResponse" class="response" style="display: none;"></div>
        </div>

        <!-- Connexion -->
        <div id="login" class="tab-content">
            <h2>🔑 Connexion</h2>
            <form id="loginForm">
                <div class="form-group">
                    <label for="login_email">Email</label>
                    <input type="email" id="login_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="login_password">Mot de passe</label>
                    <input type="password" id="login_password" name="password" required>
                </div>
                <button type="submit" class="btn">Se connecter</button>
            </form>
            <div id="loginResponse" class="response" style="display: none;"></div>
            <div id="tokenDisplay" class="token-display" style="display: none;">
                <h4>Token d'accès :</h4>
                <div id="tokenText" class="token-text"></div>
            </div>
        </div>

        <!-- Vérification -->
        <div id="verify" class="tab-content">
            <h2>✅ Vérification du code</h2>
            <form id="verifyForm">
                <div class="form-group">
                    <label for="verify_email">Email</label>
                    <input type="email" id="verify_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="verify_code">Code de vérification (6 caractères)</label>
                    <input type="text" id="verify_code" name="code" maxlength="6" required>
                </div>
                <button type="submit" class="btn">Vérifier</button>
            </form>
            <div id="verifyResponse" class="response" style="display: none;"></div>
        </div>

        <!-- Profil -->
        <div id="profile" class="tab-content">
            <h2>👤 Profil utilisateur</h2>
            <p>Vous devez être connecté pour voir votre profil.</p>
            <button type="button" class="btn" onclick="getProfile()">Afficher mon profil</button>
            <button type="button" class="btn" onclick="logout()"
                style="background: #dc3545; margin-top: 10px;">Se déconnecter</button>

            <div id="profileInfo" class="profile-info" style="display: none;">
                <h3>📋 Informations du profil</h3>
                <div class="profile-grid">
                    <div class="profile-item">
                        <span class="profile-label">👤 Nom :</span>
                        <span class="profile-value" id="profileName"></span>
                    </div>
                    <div class="profile-item">
                        <span class="profile-label">✉️ Email :</span>
                        <span class="profile-value" id="profileEmail"></span>
                    </div>
                    <div class="profile-item">
                        <span class="profile-label">📞 Téléphone :</span>
                        <span class="profile-value" id="profileTelephone"></span>
                    </div>
                    <div class="profile-item">
                        <span class="profile-label">🛡️ Rôle :</span>
                        <span class="profile-value" id="profileRole"></span>
                    </div>
                    <div class="profile-item">
                        <span class="profile-label">🔑 Permissions :</span>
                        <span class="profile-value" id="profilePermissions"></span>
                    </div>
                    <div class="profile-item">
                        <span class="profile-label">✅ Email vérifié :</span>
                        <span class="profile-value" id="profileEmailVerified"></span>
                    </div>
                    <div class="profile-item">
                        <span class="profile-label">📊 Statut :</span>
                        <span class="profile-value" id="profileStatus"></span>
                    </div>
                    <div class="profile-item">
                        <span class="profile-label">🌐 Langue :</span>
                        <span class="profile-value" id="profileLanguage"></span>
                    </div>
                    <div class="profile-item">
                        <span class="profile-label">🔔 Notifications :</span>
                        <span class="profile-value" id="profileNotifications"></span>
                    </div>
                    <div class="profile-item">
                        <span class="profile-label">🕒 Dernière connexion :</span>
                        <span class="profile-value" id="profileLastLogin"></span>
                    </div>
                    <div class="profile-item">
                        <span class="profile-label">📅 Créé le :</span>
                        <span class="profile-value" id="profileCreatedAt"></span>
                    </div>
                    <div class="profile-item">
                        <span class="profile-label">🔄 Mis à jour le :</span>
                        <span class="profile-value" id="profileUpdatedAt"></span>
                    </div>
                </div>
            </div>

            <div id="profileResponse" class="response" style="display: none;"></div>
        </div>

        <!-- Reset Password -->
        <div id="reset" class="tab-content">
            <h2>🔄 Réinitialisation du mot de passe</h2>
            <div class="grid">
                <div class="card">
                    <h3>Demander un lien de réinitialisation</h3>
                    <form id="resetForm">
                        <div class="form-group">
                            <label for="reset_email">Email</label>
                            <input type="email" id="reset_email" name="email" required>
                        </div>
                        <button type="submit" class="btn">Envoyer le lien</button>
                    </form>
                    <div id="resetResponse" class="response" style="display: none;"></div>
                </div>
                <div class="card">
                    <h3>Nouveau mot de passe</h3>
                    <form id="updatePasswordForm">
                        <div class="form-group">
                            <label for="update_email">Email</label>
                            <input type="email" id="update_email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="update_password">Nouveau mot de passe</label>
                            <input type="password" id="update_password" name="password" required>
                        </div>
                        <button type="submit" class="btn">Mettre à jour</button>
                    </form>
                    <div id="updatePasswordResponse" class="response" style="display: none;"></div>
                </div>
            </div>
        </div>


        <!-- Utilisateurs -->
        <div id="users" class="tab-content">
            <h2>👥 Gestion des utilisateurs</h2>
            <p>Fonctionnalités réservées aux Super Administrateurs</p>

            <div class="admin-actions">
                <button type="button" class="btn" onclick="getAllUsers()">📋 Lister tous les
                    utilisateurs</button>
                <button type="button" class="btn" onclick="showUserForm()" style="background: #28a745;">➕
                    Modifier un utilisateur</button>
                <button type="button" class="btn" onclick="showDeleteForm()" style="background: #dc3545;">🗑️
                    Supprimer un utilisateur</button>
            </div>

            <!-- Liste des utilisateurs -->
            <div id="usersList" class="users-list" style="display: none;">
                <h3>📋 Liste des utilisateurs</h3>
                <div id="usersTable"></div>
            </div>

            <!-- Formulaire de modification -->
            <div id="userUpdateForm" class="user-form" style="display: none;">
                <h3>✏️ Modifier un utilisateur</h3>
                <form id="updateUserForm">
                    <div class="form-group">
                        <label for="update_user_id">ID Utilisateur</label>
                        <input type="text" id="update_user_id" name="id" required
                            placeholder="ID de l'utilisateur à modifier">
                    </div>
                    <div class="form-group">
                        <label for="update_user_name">Nom complet</label>
                        <input type="text" id="update_user_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="update_user_email">Email</label>
                        <input type="email" id="update_user_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="update_user_telephone">Téléphone</label>
                        <input type="text" id="update_user_telephone" name="telephone" required>
                    </div>
                    <div class="form-group">
                        <label for="update_user_role">Rôle</label>
                        <select id="update_user_role" name="role">
                            <option value="operateur_entreprise">Opérateur Entreprise</option>
                            <option value="consultant_entreprise">Consultant Entreprise</option>
                            <option value="admin_entreprise">Admin Entreprise</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="update_user_permissions">Permissions</label>
                        <select id="update_user_permissions" name="permissions">
                            <option value="all_permissions">Toutes les permissions</option>
                            <option value="limited_permissions">Permissions limitées</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="update_user_statut">Statut</label>
                        <select id="update_user_statut" name="statut">
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="update_user_langue">Langue préférée</label>
                        <select id="update_user_langue" name="langue_preferee">
                            <option value="fr">Français</option>
                            <option value="en">Anglais</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="update_user_notifications">Préférences notifications</label>
                        <select id="update_user_notifications" name="preferences_notifications">
                            <option value="email_marketing">Email Marketing</option>
                            <option value="notifications">Notifications</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="update_user_photo">Photo de profil</label>
                        <input type="file" id="update_user_photo" name="photo_profil"
                            accept="image/jpeg,image/jpg,image/png">
                        <small style="color: #666; font-size: 0.8rem;">Formats acceptés: JPG, JPEG, PNG (max
                            3MB)</small>
                    </div>
                    <button type="submit" class="btn">Mettre à jour l'utilisateur</button>
                </form>
            </div>

            <!-- Formulaire de suppression -->
            <div id="userDeleteForm" class="user-form" style="display: none;">
                <h3>🗑️ Supprimer un utilisateur</h3>
                <div class="warning-box">
                    <p>⚠️ <strong>Attention :</strong> La suppression d'un utilisateur est irréversible !</p>
                </div>
                <form id="deleteUserForm">
                    <div class="form-group">
                        <label for="delete_user_id">ID Utilisateur à supprimer</label>
                        <input type="text" id="delete_user_id" name="id" required
                            placeholder="ID de l'utilisateur à supprimer">
                    </div>
                    <button type="submit" class="btn" style="background: #dc3545;">Supprimer
                        définitivement</button>
                </form>
            </div>

            <!-- Change Password -->
            <div id="change" class="tab-content">
                <h2>🔒 Changer le mot de passe</h2>
                <p>Vous devez être connecté pour changer votre mot de passe.</p>
                <form id="changePasswordForm">
                    <div class="form-group">
                        <label for="old_password">Ancien mot de passe</label>
                        <input type="password" id="old_password" name="old_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <button type="submit" class="btn">Changer le mot de passe</button>
                </form>
                <div id="changePasswordResponse" class="response" style="display: none;"></div>
            </div>
        </div>

        <script>
            // Configuration de l'API
            const API_BASE_URL = 'http://127.0.0.1:8000/api'; // Ajustez selon votre configuration
            let authToken = localStorage.getItem('auth_token');

            // Gestion des onglets
            function showTab(tabName) {
                // Masquer tous les onglets
                const tabs = document.querySelectorAll('.tab-content');
                tabs.forEach(tab => tab.classList.remove('active'));

                // Désactiver tous les boutons
                const buttons = document.querySelectorAll('.tab-button');
                buttons.forEach(button => button.classList.remove('active'));

                // Activer l'onglet sélectionné
                document.getElementById(tabName).classList.add('active');
                event.target.classList.add('active');
            }

            // Fonction utilitaire pour afficher les réponses
            function showResponse(elementId, data, isError = false) {
                const element = document.getElementById(elementId);
                element.style.display = 'block';
                element.className = `response ${isError ? 'error' : 'success'}`;
                element.textContent = JSON.stringify(data, null, 2);
            }

            // Fonction utilitaire pour faire des requêtes API
            async function makeRequest(url, method = 'GET', data = null, requiresAuth = false) {
                const config = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                };

                if (requiresAuth && authToken) {
                    config.headers['Authorization'] = `Bearer ${authToken}`;
                }

                if (data) {
                    config.body = JSON.stringify(data);
                }

                try {
                    const response = await fetch(url, config);
                    const responseData = await response.json();
                    return {
                        success: response.ok,
                        data: responseData,
                        status: response.status
                    };
                } catch (error) {
                    return {
                        success: false,
                        data: {
                            message: 'Erreur réseau: ' + error.message
                        },
                        status: 500
                    };
                }
            }

            // Inscription
            document.getElementById('registerForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                const data = Object.fromEntries(formData);

                const result = await makeRequest(`${API_BASE_URL}/register`, 'POST', data);
                showResponse('registerResponse', result.data, !result.success);
            });

            // Connexion
            document.getElementById('loginForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                const data = Object.fromEntries(formData);

                const result = await makeRequest(`${API_BASE_URL}/login`, 'POST', data);
                showResponse('loginResponse', result.data, !result.success);

                if (result.success && result.data.token) {
                    authToken = result.data.token;
                    localStorage.setItem('auth_token', authToken);

                    // Afficher le token
                    document.getElementById('tokenDisplay').style.display = 'block';
                    document.getElementById('tokenText').textContent = authToken;
                }
            });

            // Vérification
            document.getElementById('verifyForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                const data = Object.fromEntries(formData);

                const result = await makeRequest(`${API_BASE_URL}/verify_code`, 'POST', data);
                showResponse('verifyResponse', result.data, !result.success);

                if (result.success && result.data.access_token) {
                    authToken = result.data.access_token;
                    localStorage.setItem('auth_token', authToken);
                }
            });

            // Profil
            // async function getProfile() {
            //     const result = await makeRequest(`${API_BASE_URL}/token/users`, 'GET', null, true);
            //     showResponse('profileResponse', result.data, !result.success);
            // }


            async function getProfile() {
                const result = await makeRequest(`${API_BASE_URL}/token/users`, 'GET', null, true);

                if (result.success) {
                    // Masquer la réponse JSON brute
                    document.getElementById('profileResponse').style.display = 'none';

                    // Afficher les informations formatées
                    displayProfileInfo(result.data);
                } else {
                    showResponse('profileResponse', result.data, !result.success);
                    document.getElementById('profileInfo').style.display = 'none';
                }
            }

            // Fonction pour afficher les informations du profil de manière formatée
            function displayProfileInfo(userData) {
                document.getElementById('profileInfo').style.display = 'block';

                // Remplir les champs
                document.getElementById('profileName').textContent = userData.name || 'Non renseigné';
                document.getElementById('profileEmail').textContent = userData.email || 'Non renseigné';
                document.getElementById('profileTelephone').textContent = userData.telephone || 'Non renseigné';

                // Formater le rôle
                const roleElement = document.getElementById('profileRole');
                roleElement.textContent = formatRole(userData.role);
                roleElement.className = 'profile-value role';

                document.getElementById('profilePermissions').textContent = userData.permissions || 'Non renseigné';

                // Formater la vérification email
                const emailVerifiedElement = document.getElementById('profileEmailVerified');
                if (userData.email_verified_at) {
                    emailVerifiedElement.textContent = 'Oui (' + formatDate(userData.email_verified_at) + ')';
                    emailVerifiedElement.className = 'profile-value verified';
                } else {
                    emailVerifiedElement.textContent = 'Non vérifié';
                    emailVerifiedElement.className = 'profile-value not-verified';
                }

                // Formater le statut
                const statusElement = document.getElementById('profileStatus');
                statusElement.textContent = userData.statut === 'actif' ? 'Actif' : 'Inactif';
                statusElement.className = userData.statut === 'actif' ? 'profile-value status-active' :
                    'profile-value status-inactive';

                document.getElementById('profileLanguage').textContent = userData.langue_preferee === 'fr' ? 'Français' :
                    userData.langue_preferee || 'Non renseigné';
                document.getElementById('profileNotifications').textContent = userData.preferences_notifications ||
                    'Non renseigné';

                // Formater les dates
                document.getElementById('profileLastLogin').textContent = userData.date_derniere_connexion ? formatDate(userData
                    .date_derniere_connexion) : 'Jamais connecté';
                document.getElementById('profileCreatedAt').textContent = formatDate(userData.created_at);
                document.getElementById('profileUpdatedAt').textContent = formatDate(userData.updated_at);
            }

            // Fonction utilitaire pour formater les rôles
            function formatRole(role) {
                const roles = {
                    'super_admin': 'Super Administrateur',
                    'admin_entreprise': 'Administrateur Entreprise',
                    'consultant_entreprise': 'Consultant Entreprise',
                    'operateur_entreprise': 'Opérateur Entreprise'
                };
                return roles[role] || role;
            }


            function formatDate(dateString) {
                const date = new Date(dateString);
                const options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                return date.toLocaleDateString('fr-FR', options);
            }


            // Gestion des utilisateurs - Lister tous les utilisateurs
            async function getAllUsers() {
                const result = await makeRequest(`${API_BASE_URL}/users`, 'GET', null, true);

                if (result.success) {
                    displayUsersList(result.data);
                    hideUserForms();
                } else {
                    showResponse('usersResponse', result.data, true);
                    document.getElementById('usersList').style.display = 'none';
                }
            }


            // Afficher la liste des utilisateurs
            function displayUsersList(users) {
                document.getElementById('usersList').style.display = 'block';
                document.getElementById('usersResponse').style.display = 'none';

                const tableHtml = `
                <div class="users-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>Dernière connexion</th>
                                <th>Créé le</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${users.map(user => `
                                                                        <tr>
                                                                            <td style="font-family: monospace; font-size: 0.8rem;">${user.id}</td>
                                                                            <td>${user.name}</td>
                                                                            <td>${user.email}</td>
                                                                            <td>${user.telephone}</td>
                                                                            <td><span class="user-role-badge">${formatRole(user.role)}</span></td>
                                                                            <td><span class="user-status-${user.statut}">${user.statut === 'actif' ? 'Actif' : 'Inactif'}</span></td>
                                                                            <td>${user.date_derniere_connexion ? formatDate(user.date_derniere_connexion) : 'Jamais'}</td>
                                                                            <td>${formatDate(user.created_at)}</td>
                                                                        </tr>
                                                                    `).join('')}
                        </tbody>
                    </table>
                </div>
            `;

                document.getElementById('usersTable').innerHTML = tableHtml;
            }


            // Afficher le formulaire de modification
            function showUserForm() {
                hideUserForms();
                document.getElementById('userUpdateForm').style.display = 'block';
                document.getElementById('usersList').style.display = 'none';
                document.getElementById('usersResponse').style.display = 'none';
            }

            // Afficher le formulaire de suppression
            function showDeleteForm() {
                hideUserForms();
                document.getElementById('userDeleteForm').style.display = 'block';
                document.getElementById('usersList').style.display = 'none';
                document.getElementById('usersResponse').style.display = 'none';
            }

            // Masquer tous les formulaires utilisateur
            function hideUserForms() {
                document.getElementById('userUpdateForm').style.display = 'none';
                document.getElementById('userDeleteForm').style.display = 'none';
            }



            // Modifier un utilisateur
            document.getElementById('updateUserForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                const userId = formData.get('id');

                // Créer les données à envoyer
                const data = {};
                for (let [key, value] of formData.entries()) {
                    if (key !== 'id' && key !== 'photo_profil') {
                        data[key] = value;
                    }
                }

                // Gérer l'upload de fichier (simulation - dans un vrai projet, utilisez FormData)
                if (formData.get('photo_profil') && formData.get('photo_profil').size > 0) {
                    // Note: Pour un vrai upload de fichier, vous devriez utiliser FormData
                    // et configurer le Content-Type approprié
                    data.photo_profil = 'uploaded_file.jpg'; // Simulation
                }

                const result = await makeRequest(`${API_BASE_URL}/users/${userId}`, 'PUT', data, true);
                showResponse('usersResponse', result.data, !result.success);

                if (result.success) {
                    // Réinitialiser le formulaire
                    e.target.reset();
                    // Fonction utilitaire pour formater les dates
                    function formatDate(dateString) {
                        const date = new Date(dateString);
                        const options = {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        };
                        return date.toLocaleDateString('fr-FR', options);
                    }
                }
            });


            document.getElementById('deleteUserForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                const userId = formData.get('id');

                // Demander confirmation
                if (!confirm(
                        'Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
                    return;
                }

                const result = await makeRequest(`${API_BASE_URL}/users/${userId}`, 'DELETE', null, true);
                showResponse('usersResponse', result.data, !result.success);

                if (result.success) {
                    // Réinitialiser le formulaire
                    e.target.reset();
                    hideUserForms();
                }
            });

            // Déconnexion
            async function logout() {
                const result = await makeRequest(`${API_BASE_URL}/logout`, 'POST', null, true);
                showResponse('profileResponse', result.data, !result.success);

                if (result.success) {
                    authToken = null;
                    localStorage.removeItem('auth_token');
                    document.getElementById('tokenDisplay').style.display = 'none';
                }
            }

            // Reset password
            document.getElementById('resetForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                const data = Object.fromEntries(formData);

                const result = await makeRequest(`${API_BASE_URL}/password/reset`, 'POST',
                    data);
                showResponse('resetResponse', result.data, !result.success);
            });

            // Update password
            document.getElementById('updatePasswordForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                const data = Object.fromEntries(formData);

                const result = await makeRequest(`${API_BASE_URL}/password/update`, 'POST',
                    data);
                showResponse('updatePasswordResponse', result.data, !result.success);
            });

            // Change password
            document.getElementById('changePasswordForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                const data = Object.fromEntries(formData);

                const result = await makeRequest(`${API_BASE_URL}/change_password`, 'POST',
                    data, true);
                showResponse('changePasswordResponse', result.data, !result.success);
            });

            // Initialisation
            document.addEventListener('DOMContentLoaded', function() {
                // Afficher le token s'il existe
                if (authToken) {
                    document.getElementById('tokenDisplay').style.display = 'block';
                    document.getElementById('tokenText').textContent = authToken;
                }
            });
        </script>
</body>

</html>
