<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class Documentation extends CI_Model {
        # DOCU: This function will get documentation based on documentation id
        # Triggered by: (POST) docs/duplicate
        # Requires: $documentationd_id
        # Returns: { status: true/false, result: documentation record (Array), error: null }
        # Last updated at: March 15, 2023
        # Owner: Jovic
        public function getDocumentation($documentation_id){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                $get_documentation = $this->db->query("SELECT id, user_id, title, description, section_ids_order, is_archived, is_private, cache_collaborators_count FROM documentations WHERE id = ?;", $documentation_id);

                if($get_documentation->num_rows()){
                    $get_documentation = $get_documentation->result_array()[0];

                    # Check if User has access to Documentation
                    if($_SESSION["user_level_id"] == USER_LEVEL["USER"] && $get_documentation["is_private"] == TRUE_VALUE){
                        $this->load->model("Collaborator");
                        $get_collaborator = $this->Collaborator->getCollaborator(array($_SESSION["user_id"], $documentation_id));
    
                        if(!$get_collaborator["status"]){
                            throw new Exception($get_collaborator["error"]);
                        }
                    }

                    $response_data["result"] = $get_documentation;
                }
                else{
                    $response_data["error"] = "Documentation doesn't exist.";
                }
                

                $response_data["status"] = true;
            }
            catch (Exception $e) {
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }

        # DOCU: This function will get documentations of current workspace.
        # Triggered by: (GET) docs/edit, docs; (POST) docs/get
        # Requires: $params { workspace_id, is_archived, user_level_id }, $_SESSION["user_id"]
        # Optionals: $params { documentation_ids_order }, $_SESSION["user_id"]
        # Returns: { status: true/false, result: documentations record (Array), error: null }
        # Last updated at: Mar. 7, 2023
        # Owner: Jovic
        public function getDocumentations($params){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                // ! Binding an array value encloses it in a parenthesis which causes an error
                $where_conditions = "is_archived = ? ";
                $bind_params      = array($params["workspace_id"], $params["is_archived"]);

                # Set conditions and param values when user is not an admin
                if($params["user_level_id"] == USER_LEVEL["USER"]){
                    $where_conditions .= "AND (is_private = ?  OR id IN (SELECT documentation_id FROM collaborators WHERE user_id = ?)) ";
                    array_push($bind_params, FALSE_VALUE, $_SESSION["user_id"]);
                }
                
                # Only add ORDER BY clause to query if viewing active documentations
                $documentation_order = ($params["is_archived"] || $params["documentation_ids_order"] == null) ? "" : "ORDER BY FIELD (id, {$params["documentation_ids_order"]})";

                $get_documentations = $this->db->query("SELECT id, title, is_archived, is_private, cache_collaborators_count
                    FROM documentations
                    WHERE workspace_id = ? AND {$where_conditions}
                    $documentation_order", $bind_params
                );

                if($get_documentations->num_rows()){
                    $response_data["result"] = $get_documentations->result_array();
                }
                
                $response_data["status"] = true;
            }
            catch (Exception $e) {
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }

        # DOCU: This function will create/duplicate documentations and update documentation_ids_order of Workspace
        # Triggered by: (POST) docs/add, docs/duplicate
        # Requires: $params { user_id, workspace_id, title }
        # Optionals: $params { is_duplicate, documentation_id }
        # Returns: { status: true/false, result: { documentation_id }, error: null }
        # Last updated at: March 7, 2023
        # Owner: Erick, Updated by: Jovic
        public function addDocumentations($params){
            $response_data = array("status" => false, "result" => [], "error" => null);

            try {
                # Finalize bind params
                $description = isset($params["description"]) ? $params["description"] : NULL;
                $is_private  = isset($params["is_private"]) ? $params["is_private"] : YES;

                $insert_document_record = $this->db->query("
                    INSERT INTO documentations (user_id, workspace_id, title, description, is_archived, is_private, cache_collaborators_count, updated_by_user_id, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    array($params["user_id"], $params["workspace_id"], $params["title"], $description, NO, $is_private, ZERO_VALUE, $_SESSION["user_id"])
                );

                $new_documentation_id = $this->db->insert_id($insert_document_record);

                if($new_documentation_id > ZERO_VALUE){
                    $this->load->model("Workspace");
                    $workspace = $this->Workspace->getDocumentationsOrder($params["workspace_id"]);
                    
                    # Check if action is duplicating
                    if(!isset($params["is_duplicate"])){
                        $new_documents_order = (strlen($workspace["result"]["documentation_ids_order"])) ? $workspace["result"]["documentation_ids_order"].','. $new_documentation_id : $new_documentation_id;
                    }
                    else{
                        # Add documentation_id of duplicated record to Workspace's documentation_ids_order
                        $new_documents_order = explode(",", $workspace["result"]["documentation_ids_order"]);
    
                        for($document_index=0; $document_index < count($new_documents_order); $document_index++){
                            if($params["documentation_id"] == (int)$new_documents_order[$document_index]){
                                array_splice($new_documents_order, $document_index + 1, 0, "{$new_documentation_id}");
                            }
                        }
        
                        # Convert array to comma-separated string and update new_documents_order of new_documents_order
                        $new_documents_order = implode(",", $new_documents_order);
                    }

                    $update_workspace_docs_order = $this->db->query("UPDATE workspaces SET documentation_ids_order = ? WHERE id = ?", array( $new_documents_order, $params["workspace_id"]));

                    if($update_workspace_docs_order){
                        $response_data["status"] = true;
                        $response_data["result"] = array("documentation_id" => $new_documentation_id);
                    }
                }
            }
            catch (Exception $e) {
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }

        # DOCU: This function will update a documentation depending on what update_type is given
        # Triggered by: (POST) docs/update
        # Requires: $params { update_type, update_value, documentation_id }
        # Returns: { status: true/false, result: { documentation_id, update_type, updated_document, message, documentations_count }, error: null }
        # Last updated at: March 10, 2023
        # Owner: Erick, Updated by: Jovic
        public function updateDocumentations($params){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                $document = $this->db->query("SELECT * FROM documentations WHERE id = ?", $params["documentation_id"])->result_array()[0];
                
                # Check document id if existing
                if(isset($document["id"])){
                    # Double check if update_type only have this following values: "title", "is_archived", "is_private"
                    if( in_array($params["update_type"], ["title", "is_archived", "is_private", "description", "cache_collaborators_count"]) ){
                        $update_value = $params["update_value"];
                        # Set custom update_value if admin is Adding/Removing a collaborator
                        if($params["update_value"] == "add_collaborator"){
                            $update_value = (int)$document["cache_collaborators_count"] + $params["collaborator_count"];
                        }
                        else if($params["update_value"] == "remove_collaborator"){
                            $update_value = (int)$document["cache_collaborators_count"] - 1;
                        }

                        $update_document = $this->db->query("UPDATE documentations SET {$params["update_type"]} = ?, updated_by_user_id = ?, updated_at = NOW() WHERE id = ?", array($update_value, $_SESSION["user_id"], $params["documentation_id"]) );
                        
                        if($update_document){
                            $updated_document = $this->db->query("SELECT id, title, is_archived, is_private, cache_collaborators_count FROM documentations WHERE id = ?", $params["documentation_id"])->result_array();

                            $response_data["status"] = true;
                            $response_data["result"]["documentation_id"] = $updated_document[0]['id'];
                            $response_data["result"]["update_type"]      = $params["update_type"];

                            # Check if changing of privacy of documentation
                            if($params["update_type"] == "is_private"){
                                $response_data["result"]["updated_document"] = $updated_document;
                            }
                            # Check if archive / unarchive of documentation
                            elseif($params["update_type"] == "is_archived" ){
                                # Remove documentation_id from documentation_ids_order
                                $workspace = $this->db->query("SELECT documentation_ids_order FROM workspaces WHERE id = ?", $params["workspace_id"])->row();
                                $documentation_order_array = explode(",", $workspace->{"documentation_ids_order"});
                                $new_documents_order = NULL;
                                $documentations_count = 0;

                                # Process archiving of documentations and remove it from the documentation_ids_order field of workspaces
                                if($update_value == YES){
                                    if (($key = array_search($params["documentation_id"], $documentation_order_array)) !== false) {
                                        unset($documentation_order_array[$key]);
                                        $documentations_count = count($documentation_order_array);
                                    }

                                    $new_documents_order = ($documentations_count) ? implode(",", $documentation_order_array) : "";
                                }
                                # Process unarchiving of documentations then add the unarchived documentation id in documentation_ids_order field of workspaces
                                else {
                                    $new_documents_order  = ($workspace->{"documentation_ids_order"}) ? $workspace->{"documentation_ids_order"}.','. $params["documentation_id"] : $params["documentation_id"];
                                    $documentations_count = count($this->db->query("SELECT id FROM documentations WHERE is_archived = ?", YES)->result_array());
                                }

                                # Update the new order of documentations
                                $update_workspace = $this->db->query("UPDATE workspaces SET documentation_ids_order = ? WHERE id = ?", array($new_documents_order, $params["workspace_id"]));
                                
                                if($update_workspace){
                                    $response_data["result"]["message"] = ($update_value == NO) ? "You have no archived documentations yet." : "You have no documentations yet.";
                                    $response_data["result"]["documentations_count"] = $documentations_count;
                                }
                            }
                            # Check if updating cache_collaborators_count
                            else if($params["update_type"] == "cache_collaborators_count"){
                                $response_data["result"]["cache_collaborators_count"] = $update_value;
                            }
                        }
                    }
                }
            }
            catch (Exception $e) {
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }

        # DOCU: This function will duplicate a documentation
        # Triggered by: (POST) docs/duplicate
        # Requires: $documentation_id, $_SESSION["user_id", "workspace_id"]
        # Returns: { status: true/false, result: { documentation_id, duplicate_id, html }, error: null }
        # Last updated at: March 10, 2023
        # Owner: Jovic
        public function duplicateDocumentation($documentation_id){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                # Start DB transaction
                $this->db->trans_start();
                $get_documentation = $this->getDocumentation($documentation_id);

                if($get_documentation["status"]){
                    $duplicate_title  = "Copy of {$get_documentation['result']['title']}";

                    # Create new documentation
                    $duplicate_documentation = $this->addDocumentations(array(
                        "is_duplicate"      => true,
                        "documentation_id"  => $documentation_id,
                        "user_id"           => $_SESSION["user_id"],
                        "workspace_id"      => $_SESSION["workspace_id"],
                        "title"		        => $duplicate_title,
                        "description"       => $get_documentation["result"]["description"],
                        "is_private"        => $get_documentation["result"]["is_private"]
                    ));

                    if($duplicate_documentation["status"]){                        
                        # Get sections of documentation
                        $this->load->model("Section");
                        $duplicate_sections = $this->Section->duplicateSections(array(
                            "documentation_id"  => $documentation_id,
                            "duplicate_id"      => $duplicate_documentation["result"]["documentation_id"],
                            "section_ids_order" => $get_documentation["result"]["section_ids_order"]
                        ));

                        if($duplicate_sections["status"]){
                            # TODO: Also create modules, and tabs
                            $response_data["status"]                     = true;
                            $response_data["result"]["documentation_id"] = $documentation_id;
                            $response_data["result"]["duplicate_id"]     = $duplicate_documentation["result"]["documentation_id"];
                            $response_data["result"]["html"]             = $this->load->view(
                                "partials/document_block_partial.php",
                                array( "all_documentations" => [array(
                                    "id"                        => $duplicate_documentation["result"]["documentation_id"],
                                    "title"                     => $duplicate_title,
                                    "is_private"                => $get_documentation["result"]["is_private"],
                                    "is_archived"               => FALSE_VALUE,
                                    "cache_collaborators_count" => ZERO_VALUE
                                )]), 
                                true
                            );
                        }
                    }
                    else{
                        throw new Exception($duplicate_documentation["error"]);
                    }

                    # Commit changes to DB
                    $this->db->trans_complete();
                }
            }
            catch (Exception $e) {
                # Rollback changes when error occurs
			    $this->db->trans_rollback();
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }

        # DOCU: This function will delete a documentation
        # Triggered by: (POST) docs/remove
        # Requires: $params { remove_documentation_id, remove_is_archive }, $_SESSION["workspace_id"]
        # Returns: { status: true/false, result: { documentation_id }, error: null }
        # Last updated at: March 7, 2023
        # Owner: Jovic
        public function deleteDocumentation($params){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                # Start DB transaction
                $this->db->trans_start();

                # Delete collaborators of remove_documentation_id
				$this->load->model("Collaborator");
                $this->load->model("Section");
                $remove_collaborators = $this->Collaborator->removeCollaborators($params["remove_documentation_id"]);
                $remove_sections      = $this->Section->removeSections($params["remove_documentation_id"]);

                $is_valid = $remove_collaborators["status"] && $remove_sections["status"];

                if($is_valid){
                    $delete = $this->db->query("DELETE FROM documentations WHERE id = ?;", $params["remove_documentation_id"]);
    
                    if($this->db->affected_rows()){
                        # Remove remove_documentation_id in documentations_order and update documentations_order in workpsaces table
                        $this->load->model("Workspace");
                        $documentations_order = $this->Workspace->getDocumentationsOrder($_SESSION["workspace_id"]);
    
                        if($documentations_order["status"]){
                            # Remove remove_documentation_id from documentation_ids_order and update workspace record
                            $documentations_order = explode(",", $documentations_order["result"]["documentation_ids_order"]);
                            $documentations_count = count($documentations_order);
                            $documentation_index  = array_search($params["remove_documentation_id"], $documentations_order);
                            
                            if($documentation_index !== FALSE){
                                unset($documentations_order[$documentation_index]);
                                $documentations_count = count($documentations_order);
        
                                $documentations_order = ($documentations_count) ? implode(",", $documentations_order) : "";
                                $update_workpsace = $this->Workspace->updateDocumentationsIdsOrder(array("documentations_order" => $documentations_order, "workspace_id" => $_SESSION["workspace_id"]));
    
                                if(!$update_workpsace["status"]){
                                    throw new Exception($update_workpsace["error"]);
                                }
                            }
    
                            # Generate HTML for no documentations message
                            if(($params["remove_is_archived"] == FALSE_VALUE && !$documentations_count) || ($params["remove_is_archived"] == TRUE_VALUE && $params["archived_documentations"] == "0")){
                                $message = ($params["remove_is_archived"] == FALSE_VALUE) ? "You have no documentations yet." : "You have no archived documentations yet.";
        
                                $response_data["result"]["is_archived"]            = $params["remove_is_archived"];
                                $response_data["result"]["no_documentations_html"] = $this->load->view('partials/no_documentations_partial.php', array('message' => $message), true);
                            }
                            
                            $response_data["status"] = true;
                            $response_data["result"]["documentation_id"] = $params["remove_documentation_id"];

                            # Commit changes to DB
                            $this->db->trans_complete();
                        }
                        else{
                            throw new Exception($documentations_order["error"]);
                        }
                    }
                }
                else{
                    throw new Exception($delete_collaborators["error"]);
                }
            }
            catch (Exception $e) {
                # Rollback changes when error occurs
			    $this->db->trans_rollback();
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }

        # DOCU: This function will get user record of documentation owner
        # Triggered by: (GET) collaborators/get
        # Requires: $documentation_id
        # Returns: { status: true/false, result: user record, error: null }
        # Last updated at: March 8, 2023
        # Owner: Jovic
        public function getDocumentationOwner($documentation_id){
            $response_data = array("status" => false, "result" => array(), "error" => null);

            try {
                $documentation = $this->getDocumentation($documentation_id);

                if($documentation["result"]){
                    $this->load->model("User");
                    $get_owner = $this->User->getUser($documentation["result"]["user_id"]);

                    if($get_owner["result"]){
                        $response_data["result"] = $get_owner["result"];
                    }
                }
            }
            catch (Exception $e) {
                $response_data["error"] = $e->getMessage();
            }

            return $response_data;
        }
    }
?>