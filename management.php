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
        'add_contact_content'  
    );

    add_submenu_page(
        'null',    
        'List Contact',         
        'List Contact',         
        'manage_options',     
        'list-contact',    
        'list_contact_content'  
    );
    
    add_submenu_page(
        'null',    
        'Edit Contact',         
        'Edit Contact',         
        'manage_options',     
        'edit-contact',    
        'edit_contact_content'  
    );
    
    add_submenu_page(
        'null',    
        'Delete Contact',         
        'Delete Contact',         
        'manage_options',     
        'delete-contact',    
        'delete_contact_content'  
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
                        echo "<td><a href='" . admin_url('admin.php?page=list-contact&id=' . $user->ID) . "' class='button'>List Contacts</a>
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

    $countries_url = 'https://restcountries.com/v3.1/all?fields=cca2,name,idd';
    
    $person = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    $response = wp_remote_get($countries_url);

    if (is_wp_error($response)) {
        echo "Failed to fetch countries. Please try again later.";
        return;
    }

    $countries_data = wp_remote_retrieve_body($response);
    $countries = json_decode($countries_data, true);

    if (!$countries) {
        echo "No countries found.";
        return;
    }

    usort($countries, function($a, $b) {
        return strcmp($a['name']['common'], $b['name']['common']);
    });

    if (isset($_POST['submit'])) {
        $person = isset($_POST['person']) ? intval($_POST['person']) : 0;
        $country = sanitize_text_field($_POST['country']);
        $number = sanitize_text_field($_POST['number']);


        $country_code = ltrim($country, '+');


        if (preg_match('/^\d{9}$/', $number)) {
            
            $existing_contact = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM contact WHERE CountryCode = %s AND Number = %s", $country_code, $number)
            );

            if ($existing_contact) {
                echo '<div class="error"><p>Contact with the same country code and number already exists.</p></div>';
            } else {
                
                $wpdb->insert('contact', array('person' => $person, 'CountryCode' => $country_code, 'Number' => $number));
                echo '<div class="updated"><p>Contact added successfully!</p></div>';
            }
        } else {
            echo '<div class="error"><p>Number must contain exactly 9 digits.</p></div>';
        }
    }


    ?>
    <div class="wrap">
        <h1>Add New Contact</h1>
        <form method="post" action="">
            <input type="hidden" name="person" value="<?php echo $person; ?>">
            <label for="country">Country:</label>
            <select id="country" name="country">
            <?php foreach ($countries as $country) : ?>
                    <?php
                    $country_name = $country['name']['common'];
                    $country_code = $country['idd']['root'] . $country['idd']['suffixes'][0];
                    ?>
                    <option value="<?php echo $country_code; ?>">
                        <?php echo $country_name . ' (' . $country_code . ')'; ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>
            <label for="number">Number:</label>
            <input type="text" id="number" name="number" required><br><br>
            <input type="submit" name="submit" value="Add Contact" class="button button-primary">
        </form>
        <a href="<?php echo admin_url('admin.php?page=list-contact&id=' . $person); ?>" class="button">Return</a>
    </div>
    <?php
}

function list_contact_content() {
    global $wpdb;

    $person_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $contacts = $wpdb->get_results($wpdb->prepare("SELECT * FROM contact WHERE person = %d", $person_id));

    ?>
    <div id="main">
        <h1>Contact List</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Country Code</th>
                    <th>Number</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $contact) : ?>
                    <tr>
                        <td><?php echo $contact->ID; ?></td>
                        <td><?php echo $contact->CountryCode; ?></td>
                        <td><?php echo $contact->Number; ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=edit-contact&id=' . $contact->ID . '&person=' .$person_id); ?>" class="button">Edit</a>
                            <a href="<?php echo admin_url('admin.php?page=delete-contact&id=' . $contact->ID . '&person=' .$person_id); ?>" class="button" style="background-color: #ff0000; color: #fff; border-color: #ff0000;">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="<?php echo admin_url('admin.php?page=add-contact&id=' . $person_id); ?>" class="button">New Contact</a>
        <a href="<?php echo admin_url('admin.php?page=management-page'); ?>" class="button">Return</a>
    </div>
    <?php
}

function edit_contact_content() {
    global $wpdb;

    $person_id = isset($_GET['person']) ? intval($_GET['person']) : 0;
    $contact_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $contact = $wpdb->get_row($wpdb->prepare("SELECT * FROM contact WHERE ID = %d", $contact_id));

    if (!$contact) {
        echo "<p>No contact found with the specified ID.</p>";
        return;
    }

    $countries_url = 'https://restcountries.com/v3.1/all?fields=cca2,name,idd';
    $response = wp_remote_get($countries_url);

    if (is_wp_error($response)) {
        echo "Failed to fetch countries. Please try again later.";
        return;
    }

    $countries_data = wp_remote_retrieve_body($response);
    $countries = json_decode($countries_data, true);

    if (!$countries) {
        echo "No countries found.";
        return;
    }
    
    usort($countries, function($a, $b) {
        return strcmp($a['name']['common'], $b['name']['common']);
    });
    
    ?>
    <div class="wrap">
        <h1>Edit Contact</h1>
        <form method="post" action="">
            <input type="hidden" name="contact_id" value="<?php echo $contact_id; ?>">
            <label for="country">Country:</label>
            <select id="country" name="country">
            <?php foreach ($countries as $country) : ?>
                <?php
                $country_name = $country['name']['common'];
                $country_code_with_plus = $country['idd']['root'] . $country['idd']['suffixes'][0];
                $country_code = ltrim($country_code_with_plus, '+');
                $selected = ($contact->CountryCode === $country_code) ? 'selected' : '';
                ?>
                <option value="<?php echo $country_code_with_plus; ?>" <?php echo $selected; ?>>
                    <?php echo $country_name . ' (' . $country_code_with_plus . ')'; ?>
                </option>
            <?php endforeach; ?>
            </select><br><br>

            <label for="number">Number:</label>
            <input type="text" id="number" name="number" value="<?php echo $contact->Number; ?>" required><br><br>
            <input type="submit" name="submit" value="Save Changes" class="button button-primary">
        </form>
        <a href="<?php echo admin_url('admin.php?page=list-contact&id=' . $person_id); ?>" class="button">Return</a>
    </div>
    <?php

    if (isset($_POST['submit'])) {
        $contact_id = isset($_POST['contact_id']) ? intval($_POST['contact_id']) : 0;
        $country = sanitize_text_field($_POST['country']);
        $number = sanitize_text_field($_POST['number']);
        $country_code = ltrim($country, '+');

        $existing_contact = $wpdb->get_row($wpdb->prepare("SELECT * FROM contact WHERE CountryCode = %s AND Number = %s AND ID != %d", $country_code, $number, $contact_id));

        if ($existing_contact) {
            echo '<div class="error"><p>Contact with the same country code and number already exists.</p></div>';
            return;
        }
        
        if (strlen($number) !== 9 || !ctype_digit($number)) {
            echo '<div class="error"><p>Number must be 9 digits.</p></div>';
            return;
        }

        $wpdb->update('contact', array('CountryCode' => $country_code, 'Number' => $number), array('ID' => $contact_id));
        echo '<div class="updated"><p>Contact details updated successfully!</p></div>';
    }
}

function delete_contact_content() {
    global $wpdb;
    
    $person_id = isset($_GET['person']) ? intval($_GET['person']) : 0;
    $contact_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $contact = $wpdb->get_row($wpdb->prepare("SELECT * FROM contact WHERE ID = %d", $contact_id));

    if (!$contact) {
        echo "<p>No contact found with the specified ID.</p>";
        return;
    }

    if (isset($_POST['delete'])) {
        $wpdb->delete('contact', array('ID' => $contact_id));

        echo '<p>Contact deleted successfully.</p>';
        echo '<a href="' . admin_url('admin.php?page=list-contact&id=' . $person_id) . '" class="button">Return</a>';
        return;
    }
    ?>

    <div class="wrap">
        <h1>Delete Contact</h1>
        <p>Are you sure you want to delete the following Contact?</p>
        <p>ID: <?php echo $contact->ID; ?></p>
        <p>Country: <?php echo $contact->CountryCode; ?></p>
        <p>Number: <?php echo $contact->Number; ?></p>
        <form method="post" action="">
            <input type="submit" name="delete" value="Delete Contact" class="button button-primary">
        </form>
        <a href="<?php echo admin_url('admin.php?page=list-contact&id=' . $person_id); ?>" class="button">Return</a>
    </div>
    <?php
}

