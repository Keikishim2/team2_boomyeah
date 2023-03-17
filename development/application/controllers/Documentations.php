<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

	class Documentations extends CI_Controller {
		function __construct() {
			parent::__construct();

			$this->load->model("Documentation");

			// Check for User session
			if(!isset($_SESSION["user_id"])){
				redirect(base_url());
			}
		}
		
		# DOCU: This function will call getDocumentations from Documentation Model and render admin_documentations View
		# Triggered by: (GET) docs/edit
		# Requires: $_SESSION["user_level_id"]
		# Returns: admin_documentations page
		# Last updated at: Mar. 6, 2023
		# Owner: Jovic
		public function adminDocumentations(){
			# Check if user is allowed to access page
			$this->isUserAllowed();

			$all_documentations = $this->Documentation->getDocumentations($this->setGetDocumentationsParams());
	
			$this->load->view('documentations/admin_documentations', array("all_documentations" => $all_documentations["result"]));
		}

		# DOCU: This function will call getDocumentations from Documentation Model and render user_documentations View.
		# Triggered by: (GET) docs
		# Requires: $_SESSION["user_level_id"]
		# Returns: user_documentations page
		# Last updated at: Mar. 6, 2023
		# Owner: Jovic
		public function userDocumentations(){
			# Check if user is allowed to access page
			$this->isUserAllowed(false);

			$all_documentations = $this->Documentation->getDocumentations($this->setGetDocumentationsParams());

			$this->load->view('documentations/user_documentations', array("all_documentations" => $all_documentations["result"]));
		}

		# DOCU: This function will call getDocumentations() from Documentations Model and generate document_block_partial.
		# Triggered by: (POST) docs/get
		# Requires: $_POST["is_archived"]
		# Returns: admin_documentations page
		# Last updated at: Mar. 6, 2023
		# Owner: Jovic
		public function getDocumentations(){
			$response_data = array("status" => false, "result" => array(), "error" => null);

			try {
				# Check if user is allowed to do action
				$this->isUserAllowed();

				$all_documentations = $this->Documentation->getDocumentations($this->setGetDocumentationsParams($_POST));

				if($all_documentations["status"]){
					if(count($all_documentations["result"])){

						# TODO: Refactor document_block_partial to accept an array and perform loop inside it instead of calling document_block_partial multiple times.
						# Rendering the partial can also be moved to getDocumentations() in Documentation Model.
						$response_data["result"]["html"] = $this->load->view("partials/document_block_partial.php", array("all_documentations" => $all_documentations["result"]), true);
					}
					else{
						$response_data["result"]["html"] = $this->load->view(
							'partials/no_documentations_partial.php', 
							array('message' => ($_POST["is_archived"] == FALSE_VALUE) ? "You have no documentations yet." : "You have no archived documentations yet."),
							true
						);
					}

					$response_data["status"] = true;
					$response_data["result"]["is_archived"] = $_POST["is_archived"];
				}
				else {
					throw new Exception($all_documentations["error"]);
				}

			}
			catch (Exception $e) {
				$response_data["error"] = $e->getMessage();
			}

			echo json_encode($response_data);
		}

		# DOCU: This function will call addDocumentations() from Documentations Model to process adding of new documentation
		# Triggered by: (POST) docs/add
		# Requires: $_POST["document_title"]
		# Returns: { status: true/false, result: { documentation_id }, error: null }
		# Last updated at: Mar. 7, 2023
		# Owner: Erick, Updated by: Jovic
		public function addDocumentations(){
			$response_data = array("status" => false, "result" => array(), "error" => null);

			try {
				# Check if user is allowed to do action
				$this->isUserAllowed();

				if(isset($_POST["document_title"])){
					$response_data = $this->Documentation->addDocumentations(array(
						"user_id"      => $_SESSION["user_id"],
						"workspace_id" => $_SESSION["workspace_id"],
						"title"		   => $_POST["document_title"]
					));
				}
				else{
					$response_data["error"] = "Document title is required";
				}
			}
			catch (Exception $e) {
				$response_data["error"] = $e->getMessage();
			}

			echo json_encode($response_data);
		}

		# DOCU: This function will call updateDocumentations() from Documentations Model to process updating of documentation fields.
		# This will require update_type which is the field to be updated and update_value will be the new value
		# Triggered by: (POST) docs/update
		# Requires: $_POST["documentation_id", "update_type", "update_value"]; $_SESSION["user_id", "workspace_id"]
		# Returns: { status: true/false, result: { documentations_count, is_archived, html, no_documentations_html }, error: null }
		# Last updated at: Mar. 7, 2023
		# Owner: Erick, Updated by: Jovic
		public function updateDocumentations(){
			$response_data = array("status" => false, "result" => array(), "error" => null);

			try {
				# Check if user is allowed to do action
				$this->isUserAllowed();

				if(isset($_POST["documentation_id"])){
					# Process updating of documentation
					$update_documentation = $this->Documentation->updateDocumentations(array(
						"user_id"     	   => $_SESSION["user_id"],
						"workspace_id" 	   => $_SESSION["workspace_id"],
						"documentation_id" => $_POST["documentation_id"],
						"update_type" 	   => $_POST["update_type"],
						"update_value"     => $_POST["update_value"]
					));

					if($_POST["update_type"] == "is_private"){
						# Generate updated HTML for documentation
						$update_documentation["result"]["html"] = $this->load->view("partials/document_block_partial.php", array("all_documentations" => $update_documentation["result"]["updated_document"]), true);
					}
					elseif($_POST["update_type"] == "is_archived"){
						# Generate HTML for displaying that there are no documentations
						if(!$update_documentation["result"]["documentations_count"]){
							$update_documentation["result"]["is_archived"] = $_POST["update_value"];
							$update_documentation["result"]["no_documentations_html"] = $this->load->view("partials/no_documentations_partial.php", array("message" => $update_documentation["result"]["message"]), true);
						}
					}

					$response_data = $update_documentation;
				}
				else{
					$response_data["error"] = "Document id is required";
				}
			}
			catch (Exception $e) {
				$response_data["error"] = $e->getMessage();
			}

			echo json_encode($response_data);
		}

		# DOCU: This function will call duplicateDocumentation() from Workspace model
		# Triggered by: (POST) docs/duplicate
		# Requires: $_POST["documentation_id"]
		# Returns: { status: true/false, result: { documentation_id, duplicate_id, html }, error: null }
		# Last updated at: March 7, 2023
		# Owner: Jovic
		public function duplicateDocumentation(){
			$response_data = array("status" => false, "result" => array(), "error" => null);
			
			try {
				# Check if user is allowed to do action
				$this->isUserAllowed();

				$response_data = $this->Documentation->duplicateDocumentation($_POST["documentation_id"]);
			}
			catch (Exception $e) {
				$response_data["error"] = $e->getMessage();
			}

			echo json_encode($response_data);
		}

		# DOCU: This function will call deleteDocumentation() from Documentation model
		# Triggered by: (POST) docs/remove
		# Requires: $_POST["remove_documentation_id", "remove_is_archive"]
		# Returns: { status: true/false, result: { documentation_id }, error: null }
		# Last updated at: March 7, 2023
		# Owner: Jovic
		public function removeDocumentation(){
			$response_data = array("status" => false, "result" => array(), "error" => null);

			try {
				# Check if user is allowed to do action
				$this->isUserAllowed();

				if($_SESSION["user_level_id"] == USER_LEVEL["ADMIN"]){
					$response_data = $this->Documentation->deleteDocumentation($_POST);
				}
				else {
					throw new Exception("You are not allowed to do this action!");
				}
			}
			catch (Exception $e) {
				$response_data["error"] = $e->getMessage();
			}

			echo json_encode($response_data);
		}

		# DOCU: This function will call getDocumentation from Documentation Model and render admin_edit_documentation page
		# Triggered by: (GET) docs/(:any)/edit
		# Requires: $documentation_id
		# Last updated at: Mar. 9, 2023
		# Owner: Jovic
		public function getDocumentation($documentation_id){
			$documentation = $this->Documentation->getDocumentation($documentation_id);
			
			# Check if user is allowed to access page
			$this->isUserAllowed();

			if($documentation["status"] && $documentation["result"]){
				# Fetch sections
				$this->load->model("Section");
				$sections = $this->Section->getSections($documentation_id);

				$this->load->view('documentations/admin_edit_documentation', array("document_data" => $documentation["result"], "sections" => $sections["result"]));
			}
			else{
				# Confirm if we need to show error or just redirect back to dashboard
				echo "Documentation doesn't exist";
			}
		}

		# DOCU: This function will call getDocumentation from Documentation Model, getSection and getSectionTabs from Section Model
		# Triggered by: (GET) docs/(:any)/(:any)/edit
		# Requires: $documentation_id, $section_id
		# Last updated at: Mar. 15, 2023
		# Owner: Jovic
		public function getSection($documentation_id, $section_id){
			$documentation = $this->Documentation->getDocumentation($documentation_id);
			
			# Check if user is allowed to access page
			$this->isUserAllowed();

			if($documentation["status"] && $documentation["result"]){
				# Fetch sections
				$this->load->model("Section");
				$sections = $this->Section->getSection($section_id);

				if($sections["status"] && $sections["result"]){
					$modules  = $this->Section->getSectionTabs($section_id);
	
					$this->load->view('documentations/admin_edit_section', array("documentation" => $documentation["result"], "section" => $sections["result"], "modules" => $modules["result"]));
				}
				else{
					echo $sections["error"];
				}
			}
			else{
				# Confirm if we need to show error or just redirect back to dashboard
				echo $documentation["error"];
			}
		}

		# DOCU: This function will call getDocumentation from Documentation Model and render user_view_documentation page
		# Triggered by: (GET) docs/(:any)
		# Requires: $documentation_id
		# Last updated at: Mar. 14, 2023
		# Owner: Jovic
		public function userDocumentation($documentation_id){
			$documentation = $this->Documentation->getDocumentation($documentation_id);
			
			if($documentation["status"] && $documentation["result"]){
				# Fetch sections
				$this->load->model("Section");
				$sections = $this->Section->getSections($documentation_id);

				$this->load->view('documentations/user_view_documentation', array("document_data" => $documentation["result"], "sections" => $sections["result"]));
			}
			else{
				# Confirm if we need to show error or just redirect back to dashboard
				echo $documentation["error"];
			}
		}

		# DOCU: This function will call getDocumentation from Documentation Model and render user_view_section page
		# Triggered by: (GET) docs/(:any)/(:any)
		# Requires: $documentation_id, $section_id
		# Last updated at: Mar. 15, 2023
		# Owner: Jovic
		public function userSection($documentation_id, $section_id){
			$documentation = $this->Documentation->getDocumentation($documentation_id);
			
			if($documentation["status"] && $documentation["result"]){
				# Fetch sections
				$this->load->model("Section");
				$sections = $this->Section->getSection($section_id);

				if($sections["status"] && $sections["result"]){
					$modules = $this->Section->getSectionTabs($section_id);
	
					$this->load->view('documentations/user_view_section', array("documentation" => $documentation["result"], "section" => $sections["result"], "modules" => $modules["result"]));
				}
				else{
					echo $sections["error"];
				}
			}
			else{
				# Confirm if we need to show error or just redirect back to dashboard
				echo $documentation["error"];
			}
		}

		# DOCU: This function will call getDocumentationsOrder from Workspace Model and prepare params needed when fetching documentations.
		# Triggered by: adminDocumentations(), userDocumentations(), getDocumentations()
		# Requires: $_SESSION["workspace_id", "user_id", "user_level_id"]
		# Optionals: $params (is_archived)
		# Returns: array(user_id, workspace_id, user_level_id, is_archived, documentaion_ids_order)
		# Last updated at: Feb. 28, 2023
		# Owner: Jovic
		private function setGetDocumentationsParams($params = null){
			$this->load->model("Workspace");
			$get_documentations_order = $this->Workspace->getDocumentationsOrder($_SESSION["workspace_id"]);

			$get_documentations_params = array(
				"user_id"                 => $_SESSION["user_id"],
				"workspace_id"            => $_SESSION["workspace_id"],
				"user_level_id"           => $_SESSION["user_level_id"],
				"is_archived"             => isset($params["is_archived"]) ? $params["is_archived"] : FALSE_VALUE,
				"documentation_ids_order" => $get_documentations_order["result"]["documentation_ids_order"] 
			);
			
			return $get_documentations_params;
		}

		# DOCU: This function will check if user is allowed to visit a page or do an action.
		# Triggered by: GET and POST functions in Documentations Controller
		# Requires: $_SESSION["user_level_id"]; $is_admin_page
		# Last updated at: Mar. 6, 2023
		# Owner: Jovic
		private function isUserAllowed($is_admin_page = true){
			if($is_admin_page && $_SESSION["user_level_id"] == USER_LEVEL["USER"]){
				redirect("/docs");
			}
		}
	}
?>