<?php
/*
Plugin Name: Management Plugin
Description: Plugin for managing people and contacts.
Version: 1.0
Author: André Almeida
*/

function management_menu_page() {
    add_menu_page(
        'Management Page',        
        'Management',             
        'manage_options',         
        'management-page',     
        'management_page_content', 
        'dashicons-admin-generic' 
    );
    
    add_submenu_page(
        'management-page',    
        'New Person',         
        'New Person',         
        'manage_options',     
        'new-person',    
        'new_person_content'  
    );
    
    add_submenu_page(
        'null',    
        'Edit Person',         
        'Edit Person',         
        'manage_options',     
        'edit-person',    
        'edit_person_content'  
    );
    
    add_submenu_page(
        'null',    
        'Delete Person',         
        'Delete Person',         
        'manage_options',     
        'delete-person',    
        'delete_person_content'  
    );
    
    add_submenu_page(
        'null',    
        'Add Contact',         
        'Add Contact',         
        'manage_options',     
        'add-contact',    
        'add_contect_content'  
    );
}
add_action('admin_menu', 'management_menu_page');

function management_page_content() {
    global $wpdb;

    $users = $wpdb->get_results("SELECT * FROM recruitment.person WHERE visible = 1");

    ?>
    <div class="wrap">
        <h1>Management</h1>
        <div id="main">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($users as $user) {
                        echo "<tr>";
                        echo "<td>{$user->ID}</td>";
                        echo "<td>{$user->name}</td>";
                        echo "<td>{$user->email}</td>";
                        echo "<td><a href='" . admin_url('admin.php?page=add-contact&id=' . $user->ID) . "' class='button'>Add Contact</a>
                                  <a href='" . admin_url('admin.php?page=edit-person&id=' . $user->ID) . "' class='button'>Edit</a>
                                  <a style='background-color: #ff0000; color: #fff; border-color: #ff0000;' href='" . admin_url('admin.php?page=delete-person&id=' . $user->ID) . "' class='button'>Delete</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            <a href="<?php echo admin_url('admin.php?page=new-person'); ?>" class="button">New Person</a>
        </div>
    </div>
    <style>

    </style>
    <?php
}


function new_person_content() {
    global $wpdb;

    if (isset($_POST['form'])) {
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);

        $existing_email = $wpdb->get_var($wpdb->prepare("SELECT email FROM person WHERE email = %s", $email));

        if ($existing_email) {

            echo '<div class="error"><p>Email already exists. Please use a different email.</p></div>';
        } else {

            $wpdb->insert(
                'person', 
                array(
                    'name' => $name,
                    'email' => $email
                )
            );

            echo '<div class="updated"><p>New person added successfully!</p></div>';
        }
    }

    ?>
    <div class="wrap">
        <h1>New Person</h1>
        <form method="post" action="">
        <table class="form-table">
                <tr>
                    <th><label for="name">Name</label></th>
                    <td><input type="text" id="name" name="name" required></td>
                </tr>
                <tr>
                    <th><label for="email">Email</label></th>
                    <td><input type="email" id="email" name="email" required></td>
                </tr>
            </table>
        <input type="submit" name="form" value="Add Person" class="button">
    </form>
        <a href="<?php echo admin_url('admin.php?page=management-page'); ?>" class="button">Return</a>
    </div>
    <style>
    .form-table th {
        width:10px;
        padding-top: 5px; 
        padding-bottom: 5px; 
    }
    .form-table td {
        padding-top: 5px; 
        padding-bottom: 5px; 
    }
    
    .form-table input[type="text"],
    .form-table input[type="email"] {
        margin-bottom: 5px; 
    }
    
    .form-table input[type="submit"] {
        margin-top: 10px; 
    }

    </style>
    <?php
}

function edit_person_content() {
    global $wpdb;

    $person_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if (isset($_POST['submit'])) {

        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);

        $existing_person = $wpdb->get_row($wpdb->prepare("SELECT * FROM person WHERE email = %s AND ID != %d", $email, $person_id));

        if ($existing_person) {
            echo '<div class="error"><p>Email already exists for another person. Please use a different email.</p></div>';
        } else {
            $wpdb->update(
                'person',
                array(
                    'name' => $name,
                    'email' => $email
                ),
                array('ID' => $person_id),
            );

            echo '<div class="updated"><p>Person details updated successfully!</p></div>';
        }
    }

    $person_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM person WHERE ID = %d", $person_id));

    ?>
    <div class="wrap">
        <h1>Edit Person</h1>
        <form method="post" action="">
            <?php if ($person_details) : ?>
                <table class="form-table">
                    <tr>
                        <th><label for="name">Name</label></th>
                        <td><input type="text" id="name" name="name" value="<?php echo esc_attr($person_details->name); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="email">Email</label></th>
                        <td><input type="email" id="email" name="email" value="<?php echo esc_attr($person_details->email); ?>"></td>
                    </tr>
                </table>
                <input type="submit" name="submit" value="Save Changes" class="button-primary">
            <?php else : ?>
                <p>No person found with the specified ID.</p>
            <?php endif; ?>
        </form>
        <a href="<?php echo admin_url('admin.php?page=management-page'); ?>" class="button">Return</a>
    </div>
    <?php
}

function delete_person_content() {
    global $wpdb;

    $person_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM person WHERE ID = %d", $person_id));

    if (!$user) {
        echo "<p>No user found with the specified ID.</p>";
        return;
    }

    if (isset($_POST['delete'])) {
        $wpdb->update(
            'person',
            array('visible' => 0),
            array('ID' => $person_id),
            array('%d'),
            array('%d')
        );
        echo '<p>User deleted successfully.</p>';
        echo '<a href="' . admin_url('admin.php?page=management-page') . '" class="button">Return</a>';
        return;
    }
    ?>

    <div class="wrap">
        <h1>Delete Person</h1>
        <p>Are you sure you want to delete the following Person?</p>
        <p>ID: <?php echo $user->ID; ?></p>
        <p>Name: <?php echo $user->name; ?></p>
        <p>Email: <?php echo $user->email; ?></p>
        <form method="post" action="">
            <input type="submit" name="delete" value="Delete User" class="button button-primary">
        </form>
        <a href="<?php echo admin_url('admin.php?page=management-page'); ?>" class="button">Return</a>
    </div>
    <?php
}

function add_contact_content() {
    global $wpdb;
    
    
    
}

