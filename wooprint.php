<?php
/**
 * Plugin Name: WooCommerce QZ Tray Print
 * Description: Imprime automatiquement un ticket via QZ Tray pour chaque commande WooCommerce.
 * Version: 1.2
 * Author: Votre Nom
 */

if (!defined('ABSPATH')) {
    exit; // Sécurité
}

// Enregistrement du menu d'administration
function wqtp_add_admin_menu() {
    add_menu_page('QZ Tray Print', 'QZ Tray Print', 'manage_options', 'wqtp_settings', 'wqtp_settings_page');
    add_submenu_page('wqtp_settings', 'Commandes en temps réel', 'Commandes', 'manage_options', 'wqtp_orders', 'wqtp_orders_page');
}
add_action('admin_menu', 'wqtp_add_admin_menu');

// Page de configuration
function wqtp_settings_page() {
    ?>
    <div class="wrap">
        <h2>Configuration de l'impression</h2>
        <button id="connect_qz" class="button button-primary">Connexion à QZ Tray</button>
        <br><br>
        <label for="printer">Sélectionnez une imprimante :</label>
        <select id="printer" name="printer">
            <option value="">Chargement des imprimantes...</option>
        </select>
        <button id="save_printer" class="button">Enregistrer</button>
        <button id="test_print" class="button">Test d'impression</button>
        
        <div id="status_message" style="margin-top: 15px;"></div>
        
        <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.min.js"></script>
        <script src="<?php echo plugin_dir_url(__FILE__) . 'sign-message.js' ?>"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jsrsasign/11.1.0/jsrsasign-all-min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Récupérer l'imprimante enregistrée
                fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=wqtp_get_printer')
                    .then(response => response.json())
                    .then(data => {
                        if (data.printer) {
                            const printerSelect = document.getElementById('printer');
                            // On attendra que les imprimantes soient chargées pour sélectionner celle enregistrée
                            window.savedPrinter = data.printer;
                        }
                    });
                
                // Tentative de connexion automatique à QZ Tray
                if (typeof qz !== 'undefined') {
                    startQZConnection();
                } else {
                    showStatus('QZ Tray n\'est pas chargé correctement.', 'error');
                }
            });
            
            function showStatus(message, type = 'info') {
                const statusDiv = document.getElementById('status_message');
                statusDiv.innerHTML = message;
                statusDiv.className = type;
                if (type === 'error') {
                    statusDiv.style.color = 'red';
                } else if (type === 'success') {
                    statusDiv.style.color = 'green';
                }
            }

            function startQZConnection() {
    if (!qz.websocket.isActive()) {
        showStatus('Tentative de connexion à QZ Tray...');
        
        // Définir le certificat à utiliser
        qz.security.setCertificatePromise(function(resolve, reject) {
            resolve("-----BEGIN CERTIFICATE-----\n" +
                "MIIECzCCAvOgAwIBAgIGAZVZBpMaMA0GCSqGSIb3DQEBCwUAMIGiMQswCQYDVQQG\n" +
                "EwJVUzELMAkGA1UECAwCTlkxEjAQBgNVBAcMCUNhbmFzdG90YTEbMBkGA1UECgwS\n" +
                "UVogSW5kdXN0cmllcywgTExDMRswGQYDVQQLDBJRWiBJbmR1c3RyaWVzLCBMTEMx\n" +
                "HDAaBgkqhkiG9w0BCQEWDXN1cHBvcnRAcXouaW8xGjAYBgNVBAMMEVFaIFRyYXkg\n" +
                "RGVtbyBDZXJ0MB4XDTI1MDMwMTIyNDIzN1oXDTQ1MDMwMTIyNDIzN1owgaIxCzAJ\n" +
                "BgNVBAYTAlVTMQswCQYDVQQIDAJOWTESMBAGA1UEBwwJQ2FuYXN0b3RhMRswGQYD\n" +
                "VQQKDBJRWiBJbmR1c3RyaWVzLCBMTEMxGzAZBgNVBAsMElFaIEluZHVzdHJpZXMs\n" +
                "IExMQzEcMBoGCSqGSIb3DQEJARYNc3VwcG9ydEBxei5pbzEaMBgGA1UEAwwRUVog\n" +
                "VHJheSBEZW1vIENlcnQwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCs\n" +
                "SwGNL66JvW1g9ns7NqIlc2RU6jLBB5q1Z8jag+zb4Zdx3JxjtX4LZGjj8oQLGkZm\n" +
                "+UB5lrXb12CPhoPQAjA3AMtWXH8+HSoDwEBIMJBEQmXwqIWzU3FEkuVwd++znpih\n" +
                "9600dBbIssPPLOsPHFMbDsGOkF1ERj2cj6GOoUh5sBMpNs0kZeb76IwBC9c0gt7f\n" +
                "8jW2hbYBnQely7OLNv+tDRuDaqnQDaayVPUIiNxOtrD2EF39bG8/uu4E2Xc0Nvek\n" +
                "xLU8sv5FjABhHpL/v5V9Wnrt6MuW78AwmSJrqTbXbOdIX7vVnT9zaLsM8gLvLKRA\n" +
                "aX55iUtXwoV3fZAvAzA5AgMBAAGjRTBDMBIGA1UdEwEB/wQIMAYBAf8CAQEwDgYD\n" +
                "VR0PAQH/BAQDAgEGMB0GA1UdDgQWBBTBt/IO+nP9ouzEq9B7uGDbe309gzANBgkq\n" +
                "hkiG9w0BAQsFAAOCAQEATvgrurNijlAlyvVL7j7UU8IBHocmPhrWxikZPqZ/A4Co\n" +
                "XG71xcdvxWn3GED0EXK2P81Jyw4K8dTf+in8rcv4cMM+WzAUY+gyYRdfLZ270wGK\n" +
                "Dvdu1n4pgsQR0OKfrs6ygADnH29u9OmnXvcpu8t1nSQgdOZU1tm533nP2aMzj2GK\n" +
                "aQ+XAR1VYwZrMTW+2Hxtr0eikdkVlQLfU0HpMRLhvo0HRT6dm+pDYVivij7tB6OB\n" +
                "7ISsk8uIDmRpxzapK3ozMb31XwN46dg0OBkb8EMPZZ6exuebrMWsJ2Q4xgxO4WbQ\n" +
                "iByPw10xJl7j6Alw2SvKmnAaXVTfJs91z72aQ+eNUw==\n" +
                "-----END CERTIFICATE-----\n");
        });

        // Connecter à QZ Tray
        qz.websocket.connect()
            .then(() => {
                showStatus('Connecté à QZ Tray', 'success');
                loadPrinters();
            })
            .catch(err => {
                console.error("Erreur de connexion à QZ Tray:", err);
                showStatus('Impossible de se connecter à QZ Tray. Assurez-vous que QZ Tray est lancé.', 'error');
            });
    } else {
        showStatus('Déjà connecté à QZ Tray.', 'success');
        loadPrinters();
    }
}

            function loadPrinters() {
                qz.printers.find()
                    .then(printers => {
                        let printerSelect = document.getElementById('printer');
                        printerSelect.innerHTML = '';
                        
                        if (printers.length === 0) {
                            let option = document.createElement('option');
                            option.value = "";
                            option.textContent = "Aucune imprimante trouvée";
                            printerSelect.appendChild(option);
                            return;
                        }
                        
                        printers.forEach(printer => {
                            let option = document.createElement('option');
                            option.value = printer;
                            option.textContent = printer;
                            
                            // Sélectionner l'imprimante enregistrée
                            if (window.savedPrinter && window.savedPrinter === printer) {
                                option.selected = true;
                            }
                            
                            printerSelect.appendChild(option);
                        });
                    })
                    .catch(err => {
                        console.error("Erreur de récupération des imprimantes :", err);
                        showStatus("Erreur de récupération des imprimantes", 'error');
                    });
            }

            document.getElementById('connect_qz').addEventListener('click', startQZConnection);
            
            document.getElementById('save_printer').addEventListener('click', function() {
                let printer = document.getElementById('printer').value;
                if (!printer) {
                    showStatus("Veuillez sélectionner une imprimante !", 'error');
                    return;
                }
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=wqtp_save_printer&printer=' + encodeURIComponent(printer))
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showStatus("Imprimante enregistrée avec succès !", 'success');
                        } else {
                            showStatus("Erreur lors de l'enregistrement de l'imprimante", 'error');
                        }
                    })
                    .catch(err => {
                        console.error("Erreur lors de l'enregistrement :", err);
                        showStatus("Erreur lors de l'enregistrement", 'error');
                    });
            });
            
            document.getElementById('test_print').addEventListener('click', function() {
                let printer = document.getElementById('printer').value;
                if (!printer) {
                    showStatus("Veuillez sélectionner une imprimante !", 'error');
                    return;
                }

                if (!qz.websocket.isActive()) {
                    showStatus("Connexion à QZ Tray...");
                    qz.websocket.connect()
                        .then(sendTestPrint)
                        .catch(err => {
                            console.error("Erreur de connexion :", err);
                            showStatus("Impossible de se connecter à QZ Tray", 'error');
                        });
                } else {
                    sendTestPrint();
                }
                
                function sendTestPrint() {
                    showStatus("Envoi du test d'impression...");
                    qz.printers.find(printer)
                        .then(foundPrinter => {
                            let config = qz.configs.create(foundPrinter);
                            let data = [
                                "TEST D'IMPRESSION VIA QZ TRAY\n\n",
                                "--------------------------------\n\n",
                                "Date: <?php echo date('d/m/Y H:i'); ?>\n",
                                "Imprimante: " + printer + "\n\n",
                                "--------------------------------\n\n",
                                "Si vous pouvez lire ceci, l'impression fonctionne !\n\n"
                            ];
                            return qz.print(config, data);
                        })
                        .then(() => {
                            showStatus("Test d'impression envoyé avec succès !", 'success');
                        })
                        .catch(err => {
                            console.error("Erreur d'impression :", err);
                            showStatus("Une erreur est survenue lors de l'impression.", 'error');
                        });
                }
            });
        </script>
    </div>
    <?php
}

// Page des commandes en temps réel avec actualisation automatique
function wqtp_orders_page() {
    // Vérification de WooCommerce
    if (!class_exists('WooCommerce')) {
        echo '<div class="wrap"><div class="notice notice-error"><p>WooCommerce n\'est pas installé ou activé.</p></div></div>';
        return;
    }
    
    // Récupérer les commandes récentes
    $orders = wc_get_orders([
        'limit' => 10,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
    
    // Récupérer le paramètre d'impression automatique
    $enable_auto_print = get_option('wqtp_enable_auto_print', 'yes');
    
    ?>
    <div class="wrap">
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.min.js"></script>
    <script src="<?php echo plugin_dir_url(__FILE__) . 'sign-message.js' ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsrsasign/11.1.0/jsrsasign-all-min.js"></script>
        <h2>Commandes en temps réel</h2>
        
        <div class="auto-print-settings">
            <label>
                <input type="checkbox" id="auto_print_new_orders" <?php echo ($enable_auto_print === 'yes') ? 'checked' : ''; ?> />
                Imprimer automatiquement les nouvelles commandes
            </label>
            <button id="save_auto_print" class="button">Enregistrer</button>
            <span id="auto_print_status" style="margin-left: 10px;"></span>
        </div>
        
        <div class="order-actions" style="margin: 15px 0;">
            <button id="refresh_orders" class="button button-primary">Actualiser les commandes</button>
            <span id="last_checked" style="margin-left: 10px;">Dernière vérification: <?php echo date('H:i:s'); ?></span>
        </div>
        <div id="status_message" style="margin-top: 15px;"></div>
        <div id="printer" name="printer"></div>
        
        <div id="orders_container">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="orders_list">
                    <?php if (empty($orders)) : ?>
                        <tr>
                            <td colspan="6">Aucune commande récente.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($orders as $order) : ?>
                            <tr data-order-id="<?php echo $order->get_id(); ?>">
                                <td>#<?php echo $order->get_id(); ?></td>
                                <td><?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(); ?></td>
                                <td><?php echo $order->get_formatted_order_total(); ?></td>
                                <td><?php echo $order->get_date_created()->date_i18n('d/m/Y H:i'); ?></td>
                                <td><?php echo wc_get_order_status_name($order->get_status()); ?></td>
                                <td>
                                    <button class="button print-order" data-order-id="<?php echo $order->get_id(); ?>">
                                        Imprimer
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <script>
    // Variable pour stocker les IDs des commandes déjà vues
    let seenOrderIds = [];

    function showStatus(message, type = 'info') {
                const statusDiv = document.getElementById('status_message');
                statusDiv.innerHTML = message;
                statusDiv.className = type;
                if (type === 'error') {
                    statusDiv.style.color = 'red';
                } else if (type === 'success') {
                    statusDiv.style.color = 'green';
                }
            }

            function loadPrinters() {
                qz.printers.find()
                    .then(printers => {
                    console.log(printers);
                    })
                    .catch(err => {
                        console.error("Erreur de récupération des imprimantes :", err);
                        showStatus("Erreur de récupération des imprimantes", 'error');
                    });
            }


    // Fonction de connexion à QZ Tray
    function startQZConnection() {
        if (!qz.websocket.isActive()) {
            showStatus('Tentative de connexion à QZ Tray...');
            
            // Définir le certificat à utiliser
            qz.security.setCertificatePromise(function(resolve, reject) {
                resolve("-----BEGIN CERTIFICATE-----\n" +
                    "MIIECzCCAvOgAwIBAgIGAZVZBpMaMA0GCSqGSIb3DQEBCwUAMIGiMQswCQYDVQQG\n" +
                    "EwJVUzELMAkGA1UECAwCTlkxEjAQBgNVBAcMCUNhbmFzdG90YTEbMBkGA1UECgwS\n" +
                    "UVogSW5kdXN0cmllcywgTExDMRswGQYDVQQLDBJRWiBJbmR1c3RyaWVzLCBMTEMx\n" +
                    "HDAaBgkqhkiG9w0BCQEWDXN1cHBvcnRAcXouaW8xGjAYBgNVBAMMEVFaIFRyYXkg\n" +
                    "RGVtbyBDZXJ0MB4XDTI1MDMwMTIyNDIzN1oXDTQ1MDMwMTIyNDIzN1owgaIxCzAJ\n" +
                    "BgNVBAYTAlVTMQswCQYDVQQIDAJOWTESMBAGA1UEBwwJQ2FuYXN0b3RhMRswGQYD\n" +
                    "VQQKDBJRWiBJbmR1c3RyaWVzLCBMTEMxGzAZBgNVBAsMElFaIEluZHVzdHJpZXMs\n" +
                    "IExMQzEcMBoGCSqGSIb3DQEJARYNc3VwcG9ydEBxei5pbzEaMBgGA1UEAwwRUVog\n" +
                    "VHJheSBEZW1vIENlcnQwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCs\n" +
                    "SwGNL66JvW1g9ns7NqIlc2RU6jLBB5q1Z8jag+zb4Zdx3JxjtX4LZGjj8oQLGkZm\n" +
                    "+UB5lrXb12CPhoPQAjA3AMtWXH8+HSoDwEBIMJBEQmXwqIWzU3FEkuVwd++znpih\n" +
                    "9600dBbIssPPLOsPHFMbDsGOkF1ERj2cj6GOoUh5sBMpNs0kZeb76IwBC9c0gt7f\n" +
                    "8jW2hbYBnQely7OLNv+tDRuDaqnQDaayVPUIiNxOtrD2EF39bG8/uu4E2Xc0Nvek\n" +
                    "xLU8sv5FjABhHpL/v5V9Wnrt6MuW78AwmSJrqTbXbOdIX7vVnT9zaLsM8gLvLKRA\n" +
                    "aX55iUtXwoV3fZAvAzA5AgMBAAGjRTBDMBIGA1UdEwEB/wQIMAYBAf8CAQEwDgYD\n" +
                    "VR0PAQH/BAQDAgEGMB0GA1UdDgQWBBTBt/IO+nP9ouzEq9B7uGDbe309gzANBgkq\n" +
                    "hkiG9w0BAQsFAAOCAQEATvgrurNijlAlyvVL7j7UU8IBHocmPhrWxikZPqZ/A4Co\n" +
                    "XG71xcdvxWn3GED0EXK2P81Jyw4K8dTf+in8rcv4cMM+WzAUY+gyYRdfLZ270wGK\n" +
                    "Dvdu1n4pgsQR0OKfrs6ygADnH29u9OmnXvcpu8t1nSQgdOZU1tm533nP2aMzj2GK\n" +
                    "aQ+XAR1VYwZrMTW+2Hxtr0eikdkVlQLfU0HpMRLhvo0HRT6dm+pDYVivij7tB6OB\n" +
                    "7ISsk8uIDmRpxzapK3ozMb31XwN46dg0OBkb8EMPZZ6exuebrMWsJ2Q4xgxO4WbQ\n" +
                    "iByPw10xJl7j6Alw2SvKmnAaXVTfJs91z72aQ+eNUw==\n" +
                    "-----END CERTIFICATE-----\n");
            });
            
            // Connecter à QZ Tray
            qz.websocket.connect()
                .then(() => {
                    showStatus('Connecté à QZ Tray', 'success');
                    loadPrinters();
                })
                .catch(err => {
                    console.error("Erreur de connexion à QZ Tray:", err);
                    showStatus('Impossible de se connecter à QZ Tray. Assurez-vous que QZ Tray est lancé.', 'error');
                });
        } else {
            showStatus('Déjà connecté à QZ Tray.', 'success');
            loadPrinters();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser la liste des commandes vues
        document.querySelectorAll('#orders_list tr').forEach(row => {
            const orderId = row.getAttribute('data-order-id');
            if (orderId) {
                seenOrderIds.push(orderId);
            }
        });
        
        // Ajouter les gestionnaires d'événements pour les boutons d'impression
        attachPrintHandlers();
        
        // Essayer de se connecter à QZ Tray au chargement
        startQZConnection();
        
        // Enregistrer le réglage d'impression automatique
        document.getElementById('save_auto_print').addEventListener('click', function() {
            const autoprint = document.getElementById('auto_print_new_orders').checked ? 'yes' : 'no';
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=wqtp_save_auto_print&enable=' + autoprint)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('auto_print_status').textContent = 'Paramètre enregistré !';
                        document.getElementById('auto_print_status').style.color = 'green';
                        setTimeout(() => {
                            document.getElementById('auto_print_status').textContent = '';
                        }, 3000);
                    }
                });
        });
        
        // Rafraîchir les commandes
        document.getElementById('refresh_orders').addEventListener('click', fetchNewOrders);
        
        // Rafraîchir automatiquement toutes les 30 secondes
        setInterval(fetchNewOrders, 30000);
    });
    
    function attachPrintHandlers() {
        document.querySelectorAll('.print-order').forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                printOrder(orderId);
            });
        });
    }
    
    function fetchNewOrders() {
        fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=wqtp_get_recent_orders')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour l'horodatage
                    document.getElementById('last_checked').textContent = 'Dernière vérification: ' + new Date().toLocaleTimeString();
                    
                    // Mettre à jour le tableau des commandes
                    const ordersList = document.getElementById('orders_list');
                    
                    if (data.orders.length === 0) {
                        ordersList.innerHTML = '<tr><td colspan="6">Aucune commande récente.</td></tr>';
                        return;
                    }
                    
                    const newOrdersHtml = [];
                    const newOrderIds = [];
                    
                    data.orders.forEach(order => {
                        newOrderIds.push(order.id);
                        
                        const row = `
                            <tr data-order-id="${order.id}">
                                <td>#${order.id}</td>
                                <td>${order.client}</td>
                                <td>${order.total}</td>
                                <td>${order.date}</td>
                                <td>${order.status}</td>
                                <td>
                                    <button class="button print-order" data-order-id="${order.id}">
                                        Imprimer
                                    </button>
                                </td>
                            </tr>
                        `;
                        newOrdersHtml.push(row);
                        
                        // Vérifier s'il s'agit d'une nouvelle commande et si l'impression auto est activée
                        if (!seenOrderIds.includes(order.id) && document.getElementById('auto_print_new_orders').checked) {
                            printOrder(order.id);
                        }
                    });
                    
                    ordersList.innerHTML = newOrdersHtml.join('');
                    
                    // Mettre à jour la liste des commandes vues
                    seenOrderIds = newOrderIds;
                    
                    // Rattacher les gestionnaires d'événements
                    attachPrintHandlers();
                }
            })
            .catch(err => {
                console.error("Erreur de récupération des commandes:", err);
            });
    }
    
    function printOrder(orderId) {
        // Récupérer les données de la commande
        fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=wqtp_get_order_data&order_id=' + orderId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Connexion à QZ Tray si nécessaire
                    startQZConnection();
                    
                    // Envoyer l'impression
                    sendPrint(data.order);
                } else {
                    alert("Erreur lors de la récupération des données de commande: " + data.message);
                }
            })
            .catch(err => {
                console.error("Erreur:", err);
                alert("Une erreur est survenue lors de la récupération des données.");
            });
        
        function sendPrint(order) {
            qz.printers.find(order.printer)
                .then(printer => {
                    let config = qz.configs.create(printer);
                    
                    // Création du contenu du ticket
                    let content = [
                        "BON DE COMMANDE #" + order.id + "\n\n",
                        "Date: " + order.date + "\n",
                        "Client: " + order.client + "\n",
                        "--------------------------------\n\n"
                    ];
                    
                    // Ajouter les produits
                    order.items.forEach(item => {
                        content.push(item.quantity + "x " + item.name + "\n");
                        content.push("   " + item.price + "\n");
                    });
                    
                    content.push("\n--------------------------------\n");
                    content.push("TOTAL: " + order.total + "\n\n");
                    
                    return qz.print(config, content);
                })
                .then(() => {
                    console.log("Impression envoyée avec succès !");
                })
                .catch(err => {
                    console.error("Erreur d'impression:", err);
                    alert("Une erreur est survenue lors de l'impression.");
                });
        }
    }
</script>

    </div>
    <?php
}

// Récupérer l'imprimante sauvegardée
function wqtp_get_printer() {
    wp_send_json([
        'printer' => get_option('wqtp_printer', '')
    ]);
}
add_action('wp_ajax_wqtp_get_printer', 'wqtp_get_printer');

// Sauvegarde de l'imprimante sélectionnée
function wqtp_save_printer() {
    if (isset($_GET['printer'])) {
        update_option('wqtp_printer', sanitize_text_field($_GET['printer']));
        wp_send_json(['success' => true]);
    } else {
        wp_send_json(['success' => false]);
    }
}
add_action('wp_ajax_wqtp_save_printer', 'wqtp_save_printer');

// Récupérer les données d'une commande
function wqtp_get_order_data() {
    if (!isset($_GET['order_id'])) {
        wp_send_json(['success' => false, 'message' => 'ID de commande manquant']);
        return;
    }
    
    $order_id = intval($_GET['order_id']);
    $order = wc_get_order($order_id);
    
    if (!$order) {
        wp_send_json(['success' => false, 'message' => 'Commande introuvable']);
        return;
    }
    
    $items = [];
    foreach ($order->get_items() as $item) {
        $items[] = [
            'name' => $item->get_name(),
            'quantity' => $item->get_quantity(),
            'price' => strip_tags(wc_price($item->get_total()))
        ];
    }
    
    $order_data = [
        'id' => $order->get_id(),
        'client' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        'total' => strip_tags($order->get_formatted_order_total()),
        'date' => $order->get_date_created()->date_i18n('d/m/Y H:i'),
        'items' => $items,
        'printer' => get_option('wqtp_printer', '')
    ];
    
    wp_send_json(['success' => true, 'order' => $order_data]);
}
add_action('wp_ajax_wqtp_get_order_data', 'wqtp_get_order_data');

// Sauvegarder le paramètre d'impression automatique
function wqtp_save_auto_print() {
    if (isset($_GET['enable'])) {
        update_option('wqtp_enable_auto_print', sanitize_text_field($_GET['enable']));
        wp_send_json(['success' => true]);
    } else {
        wp_send_json(['success' => false]);
    }
}
add_action('wp_ajax_wqtp_save_auto_print', 'wqtp_save_auto_print');

// Récupérer les commandes récentes
function wqtp_get_recent_orders() {
    $orders = wc_get_orders([
        'limit' => 10,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
    
    $orders_data = [];
    
    foreach ($orders as $order) {
        $orders_data[] = [
            'id' => $order->get_id(),
            'client' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'total' => strip_tags($order->get_formatted_order_total()),
            'date' => $order->get_date_created()->date_i18n('d/m/Y H:i'),
            'status' => wc_get_order_status_name($order->get_status())
        ];
    }
    
    wp_send_json([
        'success' => true,
        'orders' => $orders_data
    ]);
}
add_action('wp_ajax_wqtp_get_recent_orders', 'wqtp_get_recent_orders');

// Enqueue des scripts d'administration
function wqtp_enqueue_admin_scripts($hook) {
    if (strpos($hook, 'wqtp_settings') !== false || strpos($hook, 'wqtp_orders') !== false) {
        wp_enqueue_style('wqtp-admin-style', plugins_url('css/admin.css', __FILE__));
    }
}
add_action('admin_enqueue_scripts', 'wqtp_enqueue_admin_scripts');

// Créer le dossier CSS lors de l'activation du plugin
function wqtp_activate() {
    // Créer le dossier CSS s'il n'existe pas
    $css_dir = plugin_dir_path(__FILE__) . 'css';
    if (!file_exists($css_dir)) {
        mkdir($css_dir, 0755, true);
    }
    
    // Créer le fichier CSS
    $css_file = $css_dir . '/admin.css';
    if (!file_exists($css_file)) {
        file_put_contents($css_file, "
            .success { color: green; font-weight: bold; }
            .error { color: red; font-weight: bold; }
            .info { color: blue; }
            
            .wp-list-table th, .wp-list-table td {
                padding: 8px;
            }
            
            .auto-print-settings {
                background: #f9f9f9;
                padding: 10px;
                margin: 10px 0;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
        ");
    }
    
    // Définir les options par défaut
    add_option('wqtp_enable_auto_print', 'yes');
}
register_activation_hook(__FILE__, 'wqtp_activate');