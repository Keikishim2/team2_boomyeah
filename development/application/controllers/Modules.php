<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

	class Modules extends CI_Controller {
		function __construct() {
			parent::__construct();

			$this->load->model("Module");

			# Check for User session
			if(!isset($_SESSION["user_id"])){
				redirect(base_url());
			}
		}
		
		# DOCU: This function will call addModule() from Module Model to process adding of new module
		# Triggered by: (POST) modules/add
		# Requires: $_POST["section_id"]
		# Returns: { status: true/false, result: { html }, error: null }
		# Last updated at: Mar. 15, 2023
		# Owner: Erick
		public function addModule(){
			$response_data = array("status" => false, "result" => array(), "error" => null);

			try {
				# Check if user is allowed to do action
				$this->isUserAllowed();

				if(isset($_POST["section_id"])){
					$response_data = $this->Module->addModule($_POST);
				}
				else{
					$response_data["error"] = "Section id is required";
				}
			}
			catch (Exception $e) {
				$response_data["error"] = $e->getMessage();
			}

			echo json_encode($response_data);
		}
		
		# DOCU: This function will call addTab() from Module Model to process adding of new tab
		# Triggered by: (POST) modules/addTab
		# Requires: $_POST["module_id"]
		# Returns: { status: true/false, result: { html }, error: null }
		# Last updated at: Mar. 15, 2023
		# Owner: Erick
		public function addTab(){
			$response_data = array("status" => false, "result" => array(), "error" => null);

			try {
				# Check if user is allowed to do action
				$this->isUserAllowed();

				if(isset($_POST["module_id"])){
					$response_data = $this->Module->addTab($_POST);
				}
				else{
					$response_data["error"] = "Module id is required";
				}
			}
			catch (Exception $e) {
				$response_data["error"] = $e->getMessage();
			}

			echo json_encode($response_data);
		}

		# DOCU: This function will call updateModule() from Module Model to process updating of Module feature
		# Triggered by: (POST) modules/update
		# Requires: $_POST
		# Returns: { status: true/false, result: {}, error: null }
		# Last updated at: Mar. 15, 2023
		# Owner: Jovic
		public function updateModule(){
			$response_data = array("status" => false, "result" => array(), "error" => null);

			try {
				# Check if user is allowed to do action
				$this->isUserAllowed();

				$response_data = $this->Module->updateModule($_POST);
			}
			catch (Exception $e) {
				$response_data["error"] = $e->getMessage();
			}

			echo json_encode($response_data);
		}

		# DOCU: This function will call removeTab() from Module Model to process removing of Tabs
		# Triggered by: (POST) modules/remove_tab
		# Requires: $_POST["tab_ib"]
		# Returns: { status: true/false, result: {}, error: null }
		# Last updated at: Mar. 15, 2023
		# Owner: Jovic
		public function removeTab(){
			$response_data = array("status" => false, "result" => array(), "error" => null);

			try {
				# Check if user is allowed to do action
				$this->isUserAllowed();

				$response_data = $this->Module->removeTab($_POST);
			}
			catch (Exception $e) {
				$response_data["error"] = $e->getMessage();
			}

			echo json_encode($response_data);
		}

		# DOCU: This function will call reorderTab() from Module Model to process reordering of Tabs
		# Triggered by: (POST) modules/reorder_tab
		# Requires: $_POST["module_id"], $_POST["tab_ids_order"]
		# Returns: { status: true/false, result: {}, error: null }
		# Last updated at: Mar. 16, 2023
		# Owner: Erick
		public function reorderTab(){
			$response_data = array("status" => false, "result" => array(), "error" => null);

			try {
				# Check if user is allowed to do action
				$this->isUserAllowed();

				$response_data = $this->Module->reorderTab($_POST);
			}
			catch (Exception $e) {
				$response_data["error"] = $e->getMessage();
			}

			echo json_encode($response_data);
		}

		# DOCU: This function will call getPosts() from Module Model to process fetching of Tab Posts
		# Triggered by: (POST) modules/get_posts
		# Requires: $_POST["tab_ib"]
		# Returns: { status: true/false, result: { tab_id, html }, error: null }
		# Last updated at: Mar. 16, 2023
		# Owner: Jovic
		public function getPosts(){
			$response_data = array("status" => false, "result" => array(), "error" => null);

			try {
				$response_data = $this->Module->getPosts($_POST["tab_id"]);
			}
			catch (Exception $e) {
				$response_data["error"] = $e->getMessage();
			}

			echo json_encode($response_data);
		}

		# DOCU: This function will call addPost() from Module Model to process creating of Tab record
		# Triggered by: (POST) modules/add_post
		# Requires: $_POST["tab_id", "post_comment"]
		# Returns: { status: true/false, result: { tab_id, html }, error: null }
		# Last updated at: Mar. 16, 2023
		# Owner: Jovic
		public function addPost(){
			$response_data = array("status" => false, "result" => array(), "error" => null);

			try {
				$response_data = $this->Module->addPost($_POST);
			}
			catch (Exception $e) {
				$response_data["error"] = $e->getMessage();
			}

			echo json_encode($response_data);
		}

		# DOCU: This function will call editPost() from Module Model to process updating of Tab record
		# Triggered by: (POST) modules/update_post
		# Requires: $_POST["post_id", "post_comment"]
		# Returns: { status: true/false, result: { post_id, post_comment_id, html }, error: null }
		# Last updated at: Mar. 16, 2023
		# Owner: Jovic
		public function editPost(){
			$response_data = array("status" => false, "result" => array(), "error" => null);

			try {
				$response_data = $this->Module->editPost($_POST);
			}
			catch (Exception $e) {
				$response_data["error"] = $e->getMessage();
			}

			echo json_encode($response_data);
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