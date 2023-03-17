<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class Module extends CI_Model {
        # DOCU: This function will fetch the module data
        # Triggered by: Any models that needs to fetch data of a module
        # Requires: $module_id
        # Returns: { status: true/false, result: { module_data }, error: null }
        # Last updated at: March 15, 2023
        # Owner: Erick
        public function getModule($module_id){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                $get_module = $this->db->query("SELECT id, tab_ids_order FROM modules WHERE id = ?", $module_id);
                
                if($get_module->num_rows()){                    
                    $response_data["status"] = true;
                    $response_data["result"] = $get_module->result_array()[0];
                }
                else{
                    throw new Exception("No module found.");
                }      
            }

            catch (Exception $e) {
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }
        
        # DOCU: This function will fetch the tab data depending if in json format or normal reult
        # Triggered by: Any models that needs to fetch data of a tab
        # Requires: $tab_id
        # Returns: { status: true/false, result: { module_data }, error: null }
        # Last updated at: March 15, 2023
        # Owner: Erick
        public function getTab($tab_id, $json_format=false){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {

                $select_query_tab = ($json_format) ? 
                "SELECT
                    tabs.module_id,
                    JSON_OBJECTAGG(
                        tabs.id,
                        JSON_OBJECT(
                            'id', tabs.id,
                            'module_id', tabs.module_id,
                            'title', tabs.title,
                            'content', tabs.content,
                            'cache_posts_count', tabs.cache_posts_count,
                            'is_comments_allowed', tabs.is_comments_allowed
                        )
                    ) AS module_tabs_json 
                FROM tabs
                WHERE tabs.id = ?" : "SELECT id, module_id, title, content, cache_posts_count, is_comments_allowed FROM tabs WHERE id = ?";

                $get_tab = $this->db->query($select_query_tab, $tab_id);
                
                if($get_tab->num_rows()){                    
                    $response_data["status"] = true;
                    $response_data["result"] = $get_tab->result_array()[0];
                }
                else{
                    throw new Exception("No module found.");
                }      
            }

            catch (Exception $e) {
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }

        # DOCU: This function will add new module and initial tab of the section
        # Triggered by: (POST) module/add
        # Requires: $params { section_id }
        # Returns: { status: true/false, result: { module_id, html }, error: null }
        # Last updated at: March 15, 2023
        # Owner: Erick
        public function addModule($params){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                # Fetch section
                $this->load->model("Section");
                $section = $this->Section->getSection($params["section_id"]);

                if($section["status"]){
                    $insert_module_record = $this->db->query(
                        "INSERT INTO modules (section_id, user_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())", array($params["section_id"], $_SESSION["user_id"])
                    );

                    $new_module_id = $this->db->insert_id($insert_module_record);

                    # Check if the new section is successfully added
                    if($new_module_id > ZERO_VALUE){
                        # Create new tab after creating a new module
                        $insert_tab_record = $this->db->query("
                            INSERT INTO tabs (module_id, user_id, title, is_comments_allowed, cache_posts_count, created_at, updated_at) 
                            VALUES (?, ?, ?, ?, ?, NOW(), NOW())", array($new_module_id , $_SESSION["user_id"], "Untitled Tab", NO, ZERO_VALUE)
                        );

                        $new_tab_id = $this->db->insert_id($insert_tab_record);

                        # Check if new tab is successfully created
                        if($new_tab_id > ZERO_VALUE){
                            # Check module have tab_ids_order
                            $module = $this->getModule($new_module_id);
                            
                            if($module["status"]){
                                $new_tab_ids_order = ($module["result"]["tab_ids_order"] == NULL) ? $new_tab_id : $module["result"]["tab_ids_order"].",".$new_tab_id;
                                
                                # After new tab is created, updated the tab_ids_order of modules table
                                $update_modules_tab_order = $this->db->query("UPDATE modules SET tab_ids_order = ? WHERE id = ?", array($new_tab_ids_order, $new_module_id));

                                if($update_modules_tab_order){
                                    $section_modules = $this->Section->getSectionTabs($params["section_id"], $new_module_id);
                                    
                                    # Check if new modules exist with the data of tab json
                                    if($section_modules["status"]){
                                        $response_data["status"] = true;
                                        $response_data["result"] = array(
                                            "module_id" => $new_module_id,
                                            "html"      => $this->load->view('partials/section_page_content_partial.php', array("modules" => $section_modules["result"]), true)
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
                else{
                    throw new Exception($section["error"]);
                }   

            }

            catch (Exception $e) {
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }
        
        # DOCU: This function will add new tab in a module
        # Triggered by: (POST) module/add_tab
        # Requires: $params { section_id }
        # Returns: { status: true/false, result: { module_id, tab_id, html_tab, html_content }, error: null }
        # Last updated at: March 15, 2023
        # Owner: Erick
        public function addTab($params){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                # Fetch module
                $module = $this->getModule($params["module_id"]);

                if($module["status"]){
                    $module_id = $module["result"]["id"];

                    $insert_tab_record = $this->db->query("
                        INSERT INTO tabs (module_id, user_id, title, is_comments_allowed, cache_posts_count, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, NOW(), NOW())", array($module_id, $_SESSION["user_id"], "Untitled Tab", NO, ZERO_VALUE)
                    );

                    $new_tab_id = $this->db->insert_id($insert_tab_record);

                    # Check if new tab is successfully created
                    if($new_tab_id > ZERO_VALUE){
                        # Create new tab_ids_order in the module
                        $new_tab_ids_order = $module["result"]["tab_ids_order"].",".$new_tab_id;
                        
                        # After new tab is created, updated the tab_ids_order of modules table
                        $update_modules_tab_order = $this->db->query("UPDATE modules SET tab_ids_order = ? WHERE id = ?", array($new_tab_ids_order, $module_id));

                        if($update_modules_tab_order){
                            $new_tab_json = $this->getTab($new_tab_id, true);
                            $module_tabs_json = json_decode($new_tab_json["result"]["module_tabs_json"]);
                            
                            $response_data["status"] = true;
                            $response_data["result"] = array(
                                "module_id"     => $module_id,
                                "tab_id"        => $new_tab_id,
                                "html_tab"      => $this->load->view("partials/page_tab_item_partial.php", array("module_tabs_json" => $module_tabs_json, "tab_ids_order" => array($new_tab_id)), true),
                                "html_content"  => $this->load->view("partials/section_page_tab_partial.php", array("module_tabs_json" => $module_tabs_json, "tab_ids_order" => array($new_tab_id)), true)
                            );
                        }
                    }
                }
                else{
                    throw new Exception($module["error"]);
                }   
            }
            catch (Exception $e) {
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }

        # DOCU: This function will update Module depending on the type of $params['action'] given
        # Triggered by: (POST) module/update
        # Requires: $params {action, module_title, module_content, is_comments_allowed, tab_id }, $_SESSION["user_id"]
        # Returns: { status: true/false, result: {}, error: null }
        # Last updated at: March 15, 2023
        # Owner: Jovic
        public function updateModule($params){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                if($params["action"] == "update_module_tab"){
                    $update_tab = $this->db->query("UPDATE tabs SET title = ?, content = ?, is_comments_allowed = ?, updated_by_user_id = ?, updated_at = NOW() WHERE id = ?;", 
                    array($params["module_title"], $params["module_content"], $params["is_comments_allowed"], $_SESSION["user_id"], $params["tab_id"]));

                    if($update_tab){
                        $response_data["status"] = true;
                    }
                }
            }
            catch (Exception $e) {
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }

        # DOCU: This function will delete the tab of a module
        # Triggered by: (POST) module/remove_tab
        # Requires: $params { tab_id }
        # Returns: { status: true/false, result: { tab_id }, error: null }
        # Last updated at: March 16, 2023
        # Owner: Erick
        public function removeTab($params){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                # Start DB transaction
                $this->db->trans_start();
                $tab = $this->getTab($params["tab_id"]);

                if($tab["status"]){
                    # Delete Tab
                    $delete_section = $this->db->query("DELETE FROM tabs WHERE id = ?;", $params["tab_id"]);

                    if($delete_section){
                        $module = $this->getModule($tab["result"]["module_id"]);

                        # Remove section_id from section_ids_order and update documentation record
                        $tabs_order = explode(",", $module["result"]["tab_ids_order"]);
                        $tab_index  = array_search($params["tab_id"], $tabs_order);

                        if($tab_index !== FALSE){
                            unset($tabs_order[$tab_index]);
                            $tabs_count = count($tabs_order);
                            $tabs_order = ($tabs_count) ? implode(",", $tabs_order) : "";

                            # Update documentations section_ids_order
                            $update_module_tabs_order = $this->db->query("UPDATE modules SET tab_ids_order = ? WHERE id = ?", array($tabs_order, $tab["result"]["module_id"]));
                            
                            if($update_module_tabs_order){
                                # Check if we are deleting the last tab then remove the module also.
                                if($tabs_count == ZERO_VALUE){
                                    $delete_module = $this->db->query("DELETE FROM modules WHERE id = ?;", $tab["result"]["module_id"]);
                                }

                                # Commit changes to DB
                                $this->db->trans_complete();

                                $response_data["status"] = true;
                                $response_data["result"]["tab_id"] = $tab["result"]["id"];
                            }
                        }
                        else{
                            $this->db->trans_rollback();
                            throw new Exception("Unable to delete tab, the tab is not included in thetab)ids_order field.");
                        }
                    }
                }
            }
            catch (Exception $e) {
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }

        # DOCU: This function will reoder the tabs of a module
        # Triggered by: (POST) module/reorder_tab
        # Requires: $params { module_id, tab_ids_order }
        # Returns: { status: true/false, result: { tab_id }, error: null }
        # Last updated at: March 16, 2023
        # Owner: Erick
        public function reorderTab($params){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                $update_module_tab_ids_order = $this->db->query("UPDATE modules SET tab_ids_order = ?, updated_at = NOW() WHERE id = ?;", array($params["tab_ids_order"], $params["module_id"]));

                if($update_module_tab_ids_order){
                    $response_data["status"] = true;
                }
                else{
                    throw new Exception("Unable to update order of the tabs.");
                }
            }
            catch (Exception $e) {
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }

        # DOCU: This function will fetch Post data and generate html
        # Triggered by: (POST) modules/add_post
        # Requires: $tab_id
        # Returns: { status: true/false, result: { tab_id, html }, error: null }
        # Last updated at: March 16, 2023
        # Owner: Jovic
        public function getPost($post_id){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                $get_post = $this->db->query("
                    SELECT
                        posts.id AS post_id, posts.tab_id, users.id AS user_id, CONCAT(users.first_name, ' ', users.last_name) AS first_name, posts.updated_at AS date_posted,
                        posts.message, posts.cache_comments_count, users.profile_picture AS user_profile_pic,
                        (CASE WHEN posts.created_at != posts.updated_at THEN 1 ELSE 0 END) AS is_edited
                    FROM posts
                    INNER JOIN users ON users.id = posts.user_id
                    WHERE posts.id = ?
                    ORDER BY posts.id DESC;", $post_id
                );

                if($get_post->num_rows()){
                    $get_post = $get_post->result_array()[0];

                    $response_data["result"]["tab_id"] = $get_post["tab_id"];
                    $response_data["result"]["html"]   = $this->load->view("partials/comment_container_partial.php", array("comment_items" => [$get_post]), true);
                }

                $response_data["status"] = true;
            }
            catch (Exception $e) {
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }

        # DOCU: This function will fetch Posts in a Tab and generate html
        # Triggered by: (POST) modules/get_posts
        # Requires: $tab_id
        # Returns: { status: true/false, result: { tab_id, html }, error: null }
        # Last updated at: March 16, 2023
        # Owner: Jovic
        public function getPosts($tab_id){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                $get_posts = $this->db->query("
                    SELECT
                        posts.id AS post_id, users.id AS user_id, CONCAT(users.first_name, ' ', users.last_name) AS first_name, posts.updated_at AS date_posted,
                        posts.message, posts.cache_comments_count, users.profile_picture AS user_profile_pic,
                        (CASE WHEN posts.created_at != posts.updated_at THEN 1 ELSE 0 END) AS is_edited
                    FROM posts
                    INNER JOIN users ON users.id = posts.user_id
                    WHERE posts.tab_id = ?
                    ORDER BY posts.id DESC;", $tab_id
                );

                if($get_posts->num_rows()){
                    $response_data["result"]["tab_id"] = $tab_id;
                    $response_data["result"]["html"]   = $this->load->view("partials/comment_container_partial.php", array("comment_items" => $get_posts->result_array()), true);
                }

                $response_data["status"] = true;
            }
            catch (Exception $e) {
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }

        # DOCU: This function will create Post record and call getPost() to fetch Post record and generate html
        # Triggered by: (POST) modules/add_post
        # Requires: $params { tab_id, post_comment }, $_SESSION["user_id"]
        # Returns: { status: true/false, result: { tab_id, html }, error: null }
        # Last updated at: March 16, 2023
        # Owner: Jovic
        public function addPost($params){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                $this->db->trans_start();
                $create_post = $this->db->query("INSERT INTO posts (user_id, tab_id, message, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW());", array($_SESSION["user_id"], $params["tab_id"], $params["post_comment"]));

                if($create_post){
                    $post_id = $this->db->insert_id();
                    $update_cache_posts_count = $this->db->query("UPDATE tabs SET cache_posts_count = cache_posts_count + 1 WHERE id = ?;", $params["tab_id"]);

                    if($update_cache_posts_count){
                        $get_post = $this->getPost($post_id);

                        if($get_post["status"]){
                            $response_data = $get_post;
                            $this->db->trans_complete();
                        }
                    }
                }
            }
            catch (Exception $e) {
                $this->db->trans_rollback();
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }

        # DOCU: This function will update Post record and call getPost() to fetch Post record and generate html
        # Triggered by: (POST) modules/add_post
        # Requires: $params { post_id, post_comment }
        # Returns: { status: true/false, result: { post_id, post_comment_id, html }, error: null }
        # Last updated at: March 16, 2023
        # Owner: Jovic
        public function editPost($params){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                $this->db->trans_start();
                $update_post = $this->db->query("UPDATE posts SET message = ?, updated_at = NOW() WHERE id = ?;", array($params["post_comment"], $params["post_id"]));

                if($update_post){
                    $get_post = $this->getPost($params["post_id"]);

                    if($get_post["status"]){
                        $response_data = $get_post;
                        $response_data["result"]["post_id"]         = $params["post_id"];
                        $response_data["result"]["post_comment_id"] = $params["post_id"];
                        
                        $this->db->trans_complete();
                    }
                }
            }
            catch (Exception $e) {
                $this->db->trans_rollback();
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }
    }
?>