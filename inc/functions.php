<?php
	/**
	 * 
	 */
	class Main{
		public $hostname;
		public $db_username;
		public $db_password;
		public $dbname;
		public $conn;
		public $mysqli_conn;
		function __construct(){
			$this->hostname = "localhost";
			$this->db_username = "root";
			$this->db_password = "";
			$this->dbname = "churchaly";
			$this->conn = new PDO("mysql:host=".$this->hostname.";dbname=".$this->dbname, $this->db_username, $this->db_password);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

			// MYSQLI CONNECTION
			$mysqli_conn = new mysqli($this->hostname,$this->db_username,$this->db_password,$this->dbname); 
		} 
		function purify($str){
			$str = trim($str);
			return $str;
		}
		function purify_output($str){
			$str = htmlspecialchars(trim($str));
			return $str;
		}
		function isUserExist($user_id){
			$stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :user_id");
			$stmt->bindParam("user_id", $user_id); 
			$stmt->execute();

			if($stmt->rowCount()>0){
				return true;
			}else{
				return false;
			}
		}
		function isTableSplitExist($split_id){
			$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE id = :split_id");
			$stmt->bindParam("split_id", $split_id); 
			$stmt->execute();

			if($stmt->rowCount()>0){
				return true;
			}else{
				return false;
			}
		}
		function isTableSplitFilled($split_id){
			$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE id = :split_id");
			$stmt->bindParam("split_id", $split_id); 
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$gifter_1 = $row['gifter_1'];
			$gifter_2 = $row['gifter_2'];
			$gifter_3 = $row['gifter_3'];
			$gifter_4 = $row['gifter_4'];
			if($gifter_1 > 0 AND $gifter_2 > 0 AND $gifter_3 > 0 AND $gifter_4 > 0){
				return true;
			}else{
				return false;
			}
		}
		function getBuilderType($user_id, $split_id){
			$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE id = :split_id AND (builder_1 = :builder_1 OR builder_2 = :builder_2)");
			$stmt->bindParam("split_id", $split_id); 
			$stmt->bindParam("builder_1", $user_id); 
			$stmt->bindParam("builder_2", $user_id); 
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$builder_1 = $row['builder_1'];
			$builder_2 = $row['builder_2'];
			if ($builder_1 == $user_id) {
				return "builder_1";
			}elseif ($builder_2 == $user_id) {
				return "builder_2";
			}else{
				return "";
			}
		}
		function isLegend($user_id, $split_id){
			$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE id = :split_id AND legend = :user_id");
			$stmt->bindParam("split_id", $split_id); 
			$stmt->bindParam("user_id", $user_id);   
			$stmt->execute();
			if ($stmt->rowCount()>0) {
				return true;
			}else{
				return false;
			}
		}
		function isMyTable($user_id, $split_id){
			$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE id = :split_id AND (gifter_1 = :gifter_1 OR gifter_2 = :gifter_2 OR gifter_3 = :gifter_3 OR gifter_4 = :gifter_4 OR builder_1 = :builder_1 OR builder_2 = :builder_2 OR legend = :legend_id)");
			$stmt->bindParam("split_id", $split_id); 
			$stmt->bindParam("builder_1", $user_id); 
			$stmt->bindParam("builder_2", $user_id);
			$stmt->bindParam("gifter_1", $user_id);
			$stmt->bindParam("gifter_2", $user_id);
			$stmt->bindParam("gifter_3", $user_id); 
			$stmt->bindParam("gifter_4", $user_id);
			$stmt->bindParam("legend_id", $user_id);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				return true;
			}else{
				return false;
			}
		}
		function sendNotifications($user_id, $title, $notification, $admin){
			$stmt = $this->conn->prepare("INSERT INTO notifications (user_id, title, notification, admin, date_sent, status) VALUES (:user_id, :title, :notification, :admin, :date_sent, :status)");
			$myTimeZone = $this->getUserDetailsById($user_id, "country_code");
			$date = new DateTime("now", new DateTimeZone($this->getTimeZoneByCountryCode($myTimeZone)) );
			$date_sent = $date->format("Y-m-d H:i:s");
			$status = "";
			$stmt->bindParam("title", $title); 
			$stmt->bindParam("user_id", $user_id);
			$stmt->bindParam("notification", $notification);
			$stmt->bindParam("admin", $admin);   
			$stmt->bindParam("date_sent", $date_sent);
			$stmt->bindParam("status", $status);
		
			if ($stmt->execute()) {
				return true;
			}else{
				return false;
			}
		}
		function getMyFirstTableType($user_id, $table_id){
			$stmt = $this->conn->prepare("SELECT id FROM table_splits WHERE table_id = :table_id AND (gifter_1 = :gifter_1 OR gifter_2 = :gifter_2 OR gifter_3 = :gifter_3 OR gifter_4 = :gifter_4 OR builder_1 = :builder_1 OR builder_2 = :builder_2 OR legend = :legend_id)");
			$stmt->bindParam("table_id", $table_id); 
			$stmt->bindParam("builder_1", $user_id); 
			$stmt->bindParam("builder_2", $user_id);
			$stmt->bindParam("gifter_1", $user_id);
			$stmt->bindParam("gifter_2", $user_id);
			$stmt->bindParam("gifter_3", $user_id); 
			$stmt->bindParam("gifter_4", $user_id);
			$stmt->bindParam("legend_id", $user_id); 
			$stmt->execute();
			if ($stmt->rowCount()>0) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				return $row['id'];
			}else{
				return "";
			}
		}
		function isMyTableSplitFilled($ref_id, $split_id){
			if ($this->getBuilderType($ref_id, $split_id) == "builder_1") {
				$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE id = :split_id AND builder_1 = :builder_1");
				$stmt->bindParam("split_id", $split_id); 
				$stmt->bindParam("builder_1", $ref_id); 
				$stmt->execute();
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$gifter_1 = $row['gifter_1'];
				$gifter_2 = $row['gifter_2']; 
				if($gifter_1 > 0 AND $gifter_2 > 0){
					return true;
				}else{
					return false;
				}
			}elseif ($this->getBuilderType($ref_id, $split_id) == "builder_2") {
				$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE id = :split_id AND builder_2 = :builder_2");
				$stmt->bindParam("split_id", $split_id); 
				$stmt->bindParam("builder_2", $ref_id); 
				$stmt->execute();
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$gifter_3 = $row['gifter_3'];
				$gifter_4 = $row['gifter_4']; 
				if($gifter_3 > 0 AND $gifter_4 > 0){
					return true;
				}else{
					return false;
				}
			}elseif ($this->isLegend($ref_id, $split_id)) {
				$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE id = :split_id AND legend = :legend_id AND (gifter_1 = :empty_gifter_1 OR gifter_2 = :empty_gifter_2 OR gifter_3 = :empty_gifter_3 OR gifter_4 = :empty_gifter_4)");
				$empty_gifter_1 = 0;
				$empty_gifter_2 = 0;
				$empty_gifter_3 = 0;
				$empty_gifter_4 = 0;
				$stmt->bindParam("legend_id", $ref_id); 
				$stmt->bindParam("empty_gifter_1", $empty_gifter_1); 
				$stmt->bindParam("empty_gifter_2", $empty_gifter_2); 
				$stmt->bindParam("empty_gifter_3", $empty_gifter_3); 
				$stmt->bindParam("empty_gifter_4", $empty_gifter_4);  
				$stmt->bindParam("split_id", $split_id); 
				$stmt->execute(); 
				if($stmt->rowCount()>0){
					return false;
				}else{
					return true;
				}
			}else{
				return true;
			} 
		} 
		function isGifterBannedFromTable($gifter_id, $split_id){
			$stmt = $this->conn->prepare("SELECT * FROM banned_table_users WHERE user_id = :gifter_id AND split_id = :split_id");
			$stmt->bindParam("gifter_id", $gifter_id); 
			$stmt->bindParam("split_id", $split_id); 
			$stmt->execute();
			if ($stmt->rowCount()>0) {
				return true;
			}else{
				return false;
			}
		}
		function getGifterType($gifter_id, $split_id){
			$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE id = :split_id");
			$stmt->bindParam("split_id", $split_id);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$gifter_1 = $row['gifter_1'];
			$gifter_2 = $row['gifter_2'];
			$gifter_3 = $row['gifter_3'];
			$gifter_4 = $row['gifter_4']; 

			 
			if ($gifter_1 == $gifter_id) {
				$gifter_type = "gifter_1"; 
			}else if ($gifter_2 == $gifter_id) {
				$gifter_type = "gifter_2"; 
			}else if ($gifter_3 == $gifter_id) {
				$gifter_type = "gifter_3"; 
			}else if ($gifter_4 == $gifter_id) {
				$gifter_type = "gifter_4"; 
			}else{
				$gifter_type = "";
			}
			return $gifter_type;
		}
		function notifyMembersOfNewGifter($gifter_id, $split_id){
			//Notification Message
			$gifter_type = $this->getGifterType($gifter_id, $split_id);
			if ($gifter_type == "gifter_1" OR $gifter_type == "gifter_2" OR $gifter_type == "gifter_3" OR $gifter_type == "gifter_4") {
				$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE $gifter_type = :gifter_id AND id = :split_id");
				$stmt->bindParam("split_id", $split_id);
				$stmt->bindParam("gifter_id", $gifter_id);
				$stmt->execute();
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$gifter_1 = $row['gifter_1'];
				$gifter_2 = $row['gifter_2'];
				$gifter_3 = $row['gifter_3'];
				$gifter_4 = $row['gifter_4'];
				$builder_1 = $row['builder_1'];
				$builder_2 = $row['builder_2'];
				$legend = $row['legend']; 

				$other_gifters = array();
	  
				if ($gifter_1 != $gifter_id) {
					if ($gifter_1 != "0") {
						array_push($other_gifters, $gifter_1);
					} 
				}
				if ($gifter_2 != $gifter_id) {
					if ($gifter_2 != "0") {
						array_push($other_gifters, $gifter_2);  
					}
				}
				if ($gifter_3 != $gifter_id) {
					if ($gifter_3 != "0") {
						array_push($other_gifters, $gifter_3); 
					} 
				}
				if ($gifter_4 != $gifter_id) {
					if ($gifter_4 != "0") {
						array_push($other_gifters, $gifter_4); 
					}
				}
				if ($builder_1 != $gifter_id) {
					if ($builder_1 != "0") {
						array_push($other_gifters, $builder_1); 
					}
				}
				if ($builder_2 != $gifter_id) {
					if ($builder_2 != "0") {
						array_push($other_gifters, $builder_2); 
					}
				}
				if ($legend != $gifter_id) {
					if ($legend != "0") {
						array_push($other_gifters, $legend); 
					}
				}
				$gifters_emails = array();
				for ($i=0; $i < count($other_gifters); $i++) { 
					$this_gifter = $other_gifters[$i];
					$this_user_email = $this->getUserDetailsById($this_gifter, "email");
					array_push($gifters_emails, $this_user_email);
					$f_name = $this->getUserDetailsById($this_gifter, "f_name");
					$new_gifter_f_name = $this->getUserDetailsById($gifter_id, "f_name")." ".$this->getUserDetailsById($gifter_id, "l_name");
					$title = "New Gifter Added to your Table";
					$message = "Hi, ".$f_name.".<br> <b>".$new_gifter_f_name."</b> has been added to your table. We are creating an exciting community to help you unleash the energy of the gift.";
					$this->sendNotifications($this_gifter, $title, $message, "");
				} 
				return implode(",", $gifters_emails);
			} 
		}
		function notifyLegendOfPaymentProof($gifter_id, $split_id){
			$legend_id = $this->getTableSplitDetailById($split_id, "legend");
			$legend_name = $this->getUserDetailsById($legend_id, "f_name");
			$gifter_f_name = $this->getUserDetailsById($gifter_id, "f_name");
			$title = "New Proof of Payment Uploaded";
			$table_url = $this->getSiteUrl()."/account/?t=".$split_id;
			$message = "Hi, Legend ".$legend_name.".<br> ".$gifter_f_name." has uploaded a proof of payment. Kindly click <a href='".$table_url."'>HERE</a> to review the proof. If you received the payment kindy approve immediately to keep the community progressive.";
			$this->sendNotifications($legend_id, $title, $message, "");
		}
		function notifyGifterOfPaymentAproval($gifter_id, $split_id){
			$legend_id = $this->getTableSplitDetailById($split_id, "legend");
			$legend_name = $this->getUserDetailsById($legend_id, "f_name");
			$gifter_f_name = $this->getUserDetailsById($gifter_id, "f_name");
			$title = "Proof of Payment Approved";
			$table_url = $this->getSiteUrl()."/account/?t=".$split_id;
			$message = "Hi, Gifter ".$gifter_f_name.".<br> Legend ".$legend_name." has approved your proof of payment. Kindly click <a href='".$table_url."'>HERE</a> to view your table progress.";
			$this->sendNotifications($gifter_id, $title, $message, "");
		}
		function notifyGifterOfFakePaymentReport($gifter_id, $split_id){ 
			$legend_id = $this->getTableSplitDetailById($split_id, "legend");
			$legend_name = $this->getUserDetailsById($legend_id, "f_name");
			$gifter_f_name = $this->getUserDetailsById($gifter_id, "f_name");
			$title = "Fake Proof of Payment Report";
			$table_url = $this->getSiteUrl()."/account/?t=".$split_id;
			$message = "Hi, Gifter ".$gifter_f_name.".<br> Legend ".$legend_name." has reported your proof of payment for your <a href='".$table_url."'>table</a> as fake. <br>Fake proof of payments are against our community standard and will endanger your stay in this community if this report is true.<br><b>Take Action</b><br><a href='".$table_url."'><button class='btn btn-primary'>Pay Legend</button></a> &nbsp;&nbsp;&nbsp;<a href='".$this->getSiteUrl()."/account/report-legend?t=".$split_id."&gifter=".$gifter_id."&gifter_type=".$this->getGifterType($gifter_id, $split_id)."'><button class='btn btn-danger'>Report Legend for Falsehood</button></a>";
			$this->sendNotifications($gifter_id, $title, $message, "");
		} 
		function getNotificationCount($user_id){
			$stmt = $this->conn->prepare("SELECT * FROM notifications WHERE user_id = :user_id AND status = :unread");
			$unread = 0;
	        $stmt->bindParam("user_id", $user_id);
	        $stmt->bindParam("unread", $unread);
	        $stmt->execute();
	        $count = $stmt->rowCount();
	        if ($count>0) {
	        	return $count;
	        }else{
	        	return "";
	        } 
		}
		function approvePayment($user_id, $split_id){  

	        $stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE id = :split_id");
	        $stmt->bindParam("split_id", $split_id);
	        $stmt->execute();
	        $row = $stmt->fetch(PDO::FETCH_ASSOC);
	        $gifter_1 = $row['gifter_1'];
	        $gifter_2 = $row['gifter_2'];
	        $gifter_3 = $row['gifter_3'];
	        $gifter_4 = $row['gifter_4'];
	        $builder_1 = $row['builder_1'];
	        $builder_2 = $row['builder_2'];
	        $gifter_1_payment_proof = $row['gifter_1_payment_proof'];
	        $gifter_2_payment_proof = $row['gifter_2_payment_proof'];
	        $gifter_3_payment_proof = $row['gifter_3_payment_proof'];
	        $gifter_4_payment_proof = $row['gifter_4_payment_proof'];
	        $legend = $row['legend'];
	        $table_id = $row['table_id'];


	        $gifter = "";
	        $gifter_payment = "";
	        if ($gifter_1 == $user_id) {
	            $gifter = "gifter_1";
	            $gifter_payment = "gifter_1_payment";
	        }
	        if ($gifter_2 == $user_id) {
	            $gifter = "gifter_2";
	            $gifter_payment = "gifter_2_payment";
	        }
	        if ($gifter_3 == $user_id) {
	            $gifter = "gifter_3";
	            $gifter_payment = "gifter_3_payment";
	        }
	        if ($gifter_4 == $user_id) {
	            $gifter = "gifter_4";
	            $gifter_payment = "gifter_4_payment";
	        }

	        if ($gifter != "" AND $gifter_payment != "") {
	            $stmt2 = $this->conn->prepare("UPDATE table_splits SET $gifter_payment = :paid WHERE id = :split_id");
	            $paid = "paid";
	            $stmt2->bindParam("paid", $paid);
	            $stmt2->bindParam("split_id", $split_id);
	            $stmt2->execute();

	            //Check if Table of filled
	            $stmt3 = $this->conn->prepare("SELECT id FROM table_splits WHERE id = :split_id AND gifter_1_payment = :gifter_1_payment_paid AND gifter_2_payment = :gifter_2_payment_paid AND gifter_3_payment = :gifter_3_payment_paid AND gifter_4_payment = :gifter_4_payment_paid");
	            $gifter_1_payment_paid = "paid";
	            $gifter_2_payment_paid = "paid";
	            $gifter_3_payment_paid = "paid";
	            $gifter_4_payment_paid = "paid";
	            $stmt3->bindParam("gifter_1_payment_paid", $gifter_1_payment_paid); 
	            $stmt3->bindParam("gifter_2_payment_paid", $gifter_2_payment_paid); 
	            $stmt3->bindParam("gifter_3_payment_paid", $gifter_3_payment_paid); 
	            $stmt3->bindParam("gifter_4_payment_paid", $gifter_4_payment_paid);  
	            $stmt3->bindParam("split_id", $split_id);
	            $stmt3->execute();
	            if ($stmt3->rowCount() > 0) {
	                //Table Filled
	                //Split Table
	                    //Delete Proofs of Payments
	                    if (file_exists("../images/proof-of-payment/".$gifter_1_payment_proof) AND trim($gifter_1_payment_proof) != "") {
	                        unlink("../images/proof-of-payment/".$gifter_1_payment_proof);
	                    }
	                    if (file_exists("../images/proof-of-payment/".$gifter_2_payment_proof) AND trim($gifter_2_payment_proof) != "") {
	                        unlink("../images/proof-of-payment/".$gifter_2_payment_proof);
	                    }
	                    if (file_exists("../images/proof-of-payment/".$gifter_3_payment_proof) AND trim($gifter_3_payment_proof) != "") {
	                        unlink("../images/proof-of-payment/".$gifter_3_payment_proof);
	                    }
	                    if (file_exists("../images/proof-of-payment/".$gifter_4_payment_proof) AND trim($gifter_4_payment_proof) != "") {
	                        unlink("../images/proof-of-payment/".$gifter_4_payment_proof);
	                    } 
	                    //Move Builder 1 to Legend
	                    //Move Gifter 1 to Builder 1
	                    //Move Gifter 2 to Builder 2
	                    $stmt4 = $this->conn->prepare("UPDATE table_splits SET legend = :builder_1, builder_1 = :gifter_1,  builder_2 = :gifter_2, session_hash = :session_hash WHERE id = :split_id");
						$session_hash = md5($split_id.time().round(rand(1,100)));
						$stmt4->bindParam("builder_1",$builder_1);
						$stmt4->bindParam("gifter_1",$gifter_1);
						$stmt4->bindParam("gifter_2",$gifter_2);
						$stmt4->bindParam("split_id",$split_id);
						$stmt4->bindParam("session_hash",$session_hash);
						$stmt4->execute();
	                    // Insert into table Split 
	                        //Move Builder 2 to Legend
	                        //Move Gifter 3 to Builder 1
	                        //Move Gifter 4 to Builder 2
	                        $stmt5 = $this->conn->prepare("INSERT INTO table_splits (legend,builder_1,builder_2, table_id, session_hash) VALUES (:builder_2,:gifter_3,:gifter_4,:table_id,:session_hash)");
	                        $session_hash = md5(time().round(rand(1,70)));
	                        $stmt5->bindParam("builder_2",$builder_2);
	                        $stmt5->bindParam("gifter_3",$gifter_3);
	                        $stmt5->bindParam("gifter_4",$gifter_4); 
	                        $stmt5->bindParam("table_id",$table_id);
	                        $stmt4->bindParam("session_hash",$session_hash);
	                        $stmt5->execute();
	                    //Set Gifter 1-4 to 0 
	                    $stmt4 = $this->conn->prepare("INSERT INTO table_splits (legend,builder_1,builder_2, table_id, session_hash) VALUES (:builder_2,:gifter_3,:gifter_4,:table_id,:session_hash)");
							$session_hash = md5(time().round(rand(1,70)));
							$stmt5->bindParam("builder_2",$builder_2);
							$stmt5->bindParam("gifter_3",$gifter_3);
							$stmt5->bindParam("gifter_4",$gifter_4); 
							$stmt5->bindParam("table_id",$table_id);
							$stmt5->bindParam("session_hash",$session_hash);
							$stmt5->execute();
						//Set Gifter 1-4 to 0 
						$stmt4 = $this->conn->prepare("UPDATE table_splits SET gifter_1 = :empty_gifter_1, gifter_2 = :empty_gifter_2, gifter_3 = :empty_gifter_3, gifter_4 = :empty_gifter_4, gifter_1_payment = :empty_gifter_payment_1, gifter_2_payment = :empty_gifter_payment_2, gifter_3_payment = :empty_gifter_payment_3, gifter_4_payment = :empty_gifter_payment_4, gifter_1_payment_proof = :gifter_1_payment_proof, 	gifter_2_payment_proof = :gifter_2_payment_proof, gifter_3_payment_proof = :gifter_3_payment_proof, gifter_4_payment_proof = :gifter_4_payment_proof, gifter_1_ban_counter_report = :gifter_1_ban_counter_report, gifter_2_ban_counter_report = :gifter_2_ban_counter_report, gifter_3_ban_counter_report = :gifter_3_ban_counter_report, gifter_4_ban_counter_report = :gifter_4_ban_counter_report, gifter_1_add_date = :gifter_1_add_date, gifter_2_add_date = :gifter_2_add_date, gifter_2_add_date = :gifter_3_add_date, gifter_4_add_date = :gifter_4_add_date, gifter_1_ban_report = :gifter_1_ban_report, gifter_2_ban_report = :gifter_2_ban_report, gifter_3_ban_report = :gifter_3_ban_report, gifter_4_ban_report = :gifter_4_ban_report, gifter_1_approval_2FA = :gifter_1_approval_2FA, gifter_2_approval_2FA = :gifter_2_approval_2FA, gifter_3_approval_2FA = :gifter_3_approval_2FA, gifter_4_approval_2FA = :gifter_4_approval_2FA, gifter_1_2fa_expiry = :gifter_1_2fa_expiry, gifter_2_2fa_expiry = :gifter_2_2fa_expiry, gifter_3_2fa_expiry = :gifter_3_2fa_expiry, gifter_4_2fa_expiry = :gifter_4_2fa_expiry, gifter_1_ban_2fa = :gifter_1_ban_2fa, gifter_2_ban_2fa = :gifter_2_ban_2fa, gifter_3_ban_2fa = :gifter_3_ban_2fa, gifter_4_ban_2fa = :gifter_4_ban_2fa, gifter_1_ban_2fa_expiry = :gifter_1_ban_2fa_expiry, gifter_2_ban_2fa_expiry = :gifter_2_ban_2fa_expiry, gifter_3_ban_2fa_expiry = :gifter_3_ban_2fa_expiry, gifter_4_ban_2fa_expiry = :gifter_4_ban_2fa_expiry WHERE id = :split_id");
						$empty_gifter_1 = 0;
						$empty_gifter_2 = 0;
						$empty_gifter_3 = 0;
						$empty_gifter_4 = 0;
						$empty_gifter_payment_1 = "";
						$empty_gifter_payment_2 = "";
						$empty_gifter_payment_3 = "";
						$empty_gifter_payment_4 = "";

						$gifter_1_payment_proof = "";
						$gifter_2_payment_proof = "";
						$gifter_3_payment_proof = "";
						$gifter_4_payment_proof = "";

						$gifter_1_ban_counter_report = 0;
						$gifter_2_ban_counter_report = 0;
						$gifter_3_ban_counter_report = 0;
						$gifter_4_ban_counter_report = 0;

						$gifter_1_add_date = "0000-00-00 00:00:00";
						$gifter_2_add_date = "0000-00-00 00:00:00";
						$gifter_3_add_date = "0000-00-00 00:00:00";
						$gifter_4_add_date = "0000-00-00 00:00:00";

						$gifter_1_ban_report = "";
						$gifter_2_ban_report = "";
						$gifter_3_ban_report = "";
						$gifter_4_ban_report = "";

						$gifter_1_approval_2FA = "";
						$gifter_2_approval_2FA = "";
						$gifter_3_approval_2FA = "";
						$gifter_4_approval_2FA = "";

						$gifter_1_2fa_expiry = "0000-00-00 00:00:00";
						$gifter_2_2fa_expiry = "0000-00-00 00:00:00";
						$gifter_3_2fa_expiry = "0000-00-00 00:00:00";
						$gifter_4_2fa_expiry = "0000-00-00 00:00:00";

						$gifter_1_ban_2fa = "";
						$gifter_2_ban_2fa = "";
						$gifter_3_ban_2fa = "";
						$gifter_4_ban_2fa = "";

						$gifter_1_ban_2fa_expiry = "0000-00-00 00:00:00";
						$gifter_2_ban_2fa_expiry = "0000-00-00 00:00:00";
						$gifter_3_ban_2fa_expiry = "0000-00-00 00:00:00";
						$gifter_4_ban_2fa_expiry = "0000-00-00 00:00:00";

						$stmt4->bindParam("empty_gifter_1",$empty_gifter_1);
						$stmt4->bindParam("empty_gifter_2",$empty_gifter_2);
						$stmt4->bindParam("empty_gifter_3",$empty_gifter_3);
						$stmt4->bindParam("empty_gifter_4",$empty_gifter_4); 
						$stmt4->bindParam("empty_gifter_payment_1",$empty_gifter_payment_1); 
						$stmt4->bindParam("empty_gifter_payment_2",$empty_gifter_payment_2); 
						$stmt4->bindParam("empty_gifter_payment_3",$empty_gifter_payment_3); 
						$stmt4->bindParam("empty_gifter_payment_4",$empty_gifter_payment_4);
						$stmt4->bindParam("gifter_1_payment_proof",$gifter_1_payment_proof); 
						$stmt4->bindParam("gifter_2_payment_proof",$gifter_2_payment_proof);
						$stmt4->bindParam("gifter_3_payment_proof",$gifter_3_payment_proof);
						$stmt4->bindParam("gifter_4_payment_proof",$gifter_4_payment_proof);
						$stmt4->bindParam("split_id",$split_id);
						$stmt4->bindParam("gifter_1_ban_counter_report",$gifter_1_ban_counter_report);
						$stmt4->bindParam("gifter_2_ban_counter_report",$gifter_2_ban_counter_report);
						$stmt4->bindParam("gifter_3_ban_counter_report",$gifter_3_ban_counter_report);
						$stmt4->bindParam("gifter_4_ban_counter_report",$gifter_4_ban_counter_report);

						$stmt4->bindParam("gifter_1_add_date",$gifter_1_add_date);
						$stmt4->bindParam("gifter_2_add_date",$gifter_2_add_date);
						$stmt4->bindParam("gifter_3_add_date",$gifter_3_add_date);
						$stmt4->bindParam("gifter_4_add_date",$gifter_4_add_date);

						$stmt4->bindParam("gifter_1_ban_report",$gifter_1_ban_report);
						$stmt4->bindParam("gifter_2_ban_report",$gifter_2_ban_report);
						$stmt4->bindParam("gifter_3_ban_report",$gifter_3_ban_report);
						$stmt4->bindParam("gifter_4_ban_report",$gifter_4_ban_report);

						$stmt4->bindParam("gifter_1_approval_2FA",$gifter_1_approval_2FA);
						$stmt4->bindParam("gifter_2_approval_2FA",$gifter_2_approval_2FA);
						$stmt4->bindParam("gifter_3_approval_2FA",$gifter_3_approval_2FA);
						$stmt4->bindParam("gifter_4_approval_2FA",$gifter_4_approval_2FA);

						$stmt4->bindParam("gifter_1_2fa_expiry",$gifter_1_2fa_expiry);
						$stmt4->bindParam("gifter_2_2fa_expiry",$gifter_2_2fa_expiry);
						$stmt4->bindParam("gifter_3_2fa_expiry",$gifter_3_2fa_expiry);
						$stmt4->bindParam("gifter_4_2fa_expiry",$gifter_4_2fa_expiry);

						$stmt4->bindParam("gifter_1_ban_2fa",$gifter_1_ban_2fa);
						$stmt4->bindParam("gifter_2_ban_2fa",$gifter_2_ban_2fa);
						$stmt4->bindParam("gifter_3_ban_2fa",$gifter_3_ban_2fa);
						$stmt4->bindParam("gifter_4_ban_2fa",$gifter_4_ban_2fa);

						$stmt4->bindParam("gifter_1_ban_2fa_expiry",$gifter_1_ban_2fa_expiry);
						$stmt4->bindParam("gifter_2_ban_2fa_expiry",$gifter_2_ban_2fa_expiry);
						$stmt4->bindParam("gifter_3_ban_2fa_expiry",$gifter_3_ban_2fa_expiry);
						$stmt4->bindParam("gifter_4_ban_2fa_expiry",$gifter_4_ban_2fa_expiry);
						$stmt4->execute();

	                    return true;
	            }else{
	                //Table not filled
	                //Ignore
	                return true;
	            }
	        }
	    }
	    function banGifter($gifter_id, $split_id, $gifter_type){
	    	$gifter_payment = $gifter_type."_payment";
	    	$gifter_payment_proof = $gifter_type."_payment_proof";
	    	$gifter_ban_report = $gifter_type."_ban_report";
	    	$gifter_ban_counter_report = $gifter_type."_ban_counter_report";
	    	$ban_2fa_expiry = $gifter_type."_ban_2fa_expiry";
       		$ban_2faa = $gifter_type."_ban_2fa";
       		$gifter_add_date = $gifter_type."_add_date";

	    	$stmt = $this->conn->prepare("UPDATE table_splits SET $gifter_type = :no_gifter, $gifter_payment = :egifter_payment, $gifter_ban_report = :egifter_ban_report, $gifter_payment_proof = :egifter_payment_proof, $ban_2fa_expiry = :eban_2fa_expiry, $ban_2faa = :eban_2faa, $gifter_add_date = :egifter_add_date, $gifter_ban_counter_report = :egifter_ban_counter_report WHERE id = :split_id AND $gifter_type = :gifter_id");
	    	$no_gifter = 0;
	    	$egifter_payment_proof = "";
	    	$egifter_ban_report = ""; 
	    	$egifter_payment = ""; 
	    	$eban_2fa_expiry = "0000-00-00 00:00:00";
	    	$eban_2faa = "";
	    	$egifter_ban_counter_report = "";
	    	$egifter_add_date = "";

	    	$stmt->bindParam("split_id", $split_id); 
	    	$stmt->bindParam("no_gifter", $no_gifter); 
	    	$stmt->bindParam("gifter_id", $gifter_id); 
	    	$stmt->bindParam("egifter_payment", $egifter_payment); 
	    	$stmt->bindParam("egifter_ban_report", $egifter_ban_report); 
	    	$stmt->bindParam("egifter_payment_proof", $egifter_payment_proof); 
	    	$stmt->bindParam("eban_2fa_expiry", $eban_2fa_expiry);
	    	$stmt->bindParam("egifter_add_date", $egifter_add_date);
	    	$stmt->bindParam("egifter_ban_counter_report", $egifter_ban_counter_report);
	    	 
	    	$stmt->bindParam("eban_2faa", $eban_2faa); 
	    	$stmt->execute();
	    	
	    	$stmt1 = $this->conn->prepare("UPDATE users SET status = :banned WHERE id = :gifter_id");
	    	$banned = "banned";
	    	$stmt1->bindParam("gifter_id", $gifter_id);
	    	$stmt1->bindParam("banned", $banned);
	    	if($stmt1->execute()){
	    		return true;
	    	}else{
	    		return false;
	    	}
	    }
		function addGifter($ref_id, $gifter_id, $split_id){ 
			if (!$this->isGifterBannedFromTable($gifter_id, $split_id)) {
				if ($this->getBuilderType($ref_id, $split_id) == "builder_1") {
					$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE id = :split_id");
					$stmt->bindParam("split_id", $split_id); 
					$stmt->execute();
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					$gifter_1 = $row['gifter_1'];
					$gifter_2 = $row['gifter_2'];
					if(trim($gifter_1) == 0){
						//Free Slot Exist
						//Update Table Split
						$stmt3 = $this->conn->prepare("UPDATE table_splits SET gifter_1 = :gifter_id, gifter_1_add_date = :gifter_1_add_date WHERE id = :split_id");
						$myTimeZone = $this->getUserDetailsById($gifter_id, "country_code");
						$date = new DateTime("now", new DateTimeZone($this->getTimeZoneByCountryCode($myTimeZone)) );
						$gifter_1_add_date = $date->format("Y-m-d H:i:s"); 
						$stmt3->bindParam("gifter_1_add_date", $gifter_1_add_date);
						$stmt3->bindParam("gifter_id", $gifter_id);
						$stmt3->bindParam("split_id", $split_id); 
						if($stmt3->execute()){
							return true;
						}else{
							return false;
						}
					}elseif(trim($gifter_2) == 0){ 
						//Free Slot Exist
						//Update Table Split
						$stmt3 = $this->conn->prepare("UPDATE table_splits SET gifter_2 = :gifter_id, gifter_2_add_date = :gifter_2_add_date WHERE id = :split_id");
						$myTimeZone = $this->getUserDetailsById($gifter_id, "country_code");
						$date = new DateTime("now", new DateTimeZone($this->getTimeZoneByCountryCode($myTimeZone)) );
						$gifter_2_add_date = $date->format("Y-m-d H:i:s");
						$stmt3->bindParam("gifter_2_add_date", $gifter_2_add_date);
						$stmt3->bindParam("gifter_id", $gifter_id);
						$stmt3->bindParam("split_id", $split_id); 
						if($stmt3->execute()){
							return true;
						}else{
							return false;
						}
					}else{
						//Table Filled
						//Find Another table of same Level
						$alt_split_id = $this->getAltTableSplit($split_id);
						//Recurse addGifter
						if($alt_split_id != false){
							$this->addGifterAlt($gifter_id, $alt_split_id);
						}
					} 
				}elseif ($this->getBuilderType($ref_id, $split_id) == "builder_2") {
					$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE id = :split_id");
					$stmt->bindParam("split_id", $split_id);   
					$stmt->execute();
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					$gifter_3 = $row['gifter_3'];
					$gifter_4 = $row['gifter_4'];  
					if(trim($gifter_3) == 0){ 
						//Free Slot Exist 
						//Update Table Split
						$stmt3 = $this->conn->prepare("UPDATE table_splits SET gifter_3 = :gifter_id, gifter_3_add_date = :gifter_3_add_date WHERE id = :split_id");
						$myTimeZone = $this->getUserDetailsById($gifter_id, "country_code");
						$date = new DateTime("now", new DateTimeZone($this->getTimeZoneByCountryCode($myTimeZone)) );
						$gifter_3_add_date = $date->format("Y-m-d H:i:s"); 
						$stmt3->bindParam("gifter_3_add_date", $gifter_3_add_date);
						$stmt3->bindParam("gifter_id", $gifter_id);
						$stmt3->bindParam("split_id", $split_id); 
						if($stmt3->execute()){
							return true;
						}else{
							return false;
						}
					}elseif(trim($gifter_4) == 0){ 
						//Free Slot Exist 
						//Update Table Split
						$stmt3 = $this->conn->prepare("UPDATE table_splits SET gifter_4 = :gifter_id, gifter_4_add_date = :gifter_4_add_date WHERE id = :split_id");
						$myTimeZone = $this->getUserDetailsById($gifter_id, "country_code");
						$date = new DateTime("now", new DateTimeZone($this->getTimeZoneByCountryCode($myTimeZone)) );
						$gifter_4_add_date = $date->format("Y-m-d H:i:s");
						$stmt3->bindParam("gifter_4_add_date", $gifter_4_add_date);
						$stmt3->bindParam("gifter_id", $gifter_id);
						$stmt3->bindParam("split_id", $split_id); 
						if($stmt3->execute()){
							return true;
						}else{
							return false;
						}
					}else{
						//Table Filled
						//Find Another table of same Level
						$alt_split_id = $this->getAltTableSplit($split_id);
						//Recurse addGifter
						if($alt_split_id != false){
							$this->addGifterAlt($gifter_id, $alt_split_id);
						}
					} 
				}elseif ($this->isLegend($ref_id, $split_id)) {
					$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE id = :split_id");
					$stmt->bindParam("split_id", $split_id);  
					$stmt->execute();
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					$gifter_1 = $row['gifter_1'];
					$gifter_2 = $row['gifter_2'];
					$gifter_3 = $row['gifter_3'];
					$gifter_4 = $row['gifter_4'];  
					if(trim($gifter_1) == 0){ 
						//Free Slot Exist 
						//Update Table Split
						$stmt3 = $this->conn->prepare("UPDATE table_splits SET gifter_1 = :gifter_id, gifter_1_add_date = :gifter_1_add_date WHERE id = :split_id");
						$myTimeZone = $this->getUserDetailsById($gifter_id, "country_code");
						$date = new DateTime("now", new DateTimeZone($this->getTimeZoneByCountryCode($myTimeZone)) );
						$gifter_1_add_date = $date->format("Y-m-d H:i:s");
						$stmt3->bindParam("gifter_1_add_date", $gifter_1_add_date);
						$stmt3->bindParam("gifter_id", $gifter_id);
						$stmt3->bindParam("split_id", $split_id); 
						if($stmt3->execute()){
							return true;
						}else{
							return false;
						}
					}elseif(trim($gifter_2) == 0){ 
						//Free Slot Exist 
						//Update Table Split
						$stmt3 = $this->conn->prepare("UPDATE table_splits SET gifter_2 = :gifter_id, gifter_2_add_date = :gifter_2_add_date WHERE id = :split_id");
						$myTimeZone = $this->getUserDetailsById($gifter_id, "country_code");
						$date = new DateTime("now", new DateTimeZone($this->getTimeZoneByCountryCode($myTimeZone)) );
						$gifter_2_add_date = $date->format("Y-m-d H:i:s");
						$stmt3->bindParam("gifter_2_add_date", $gifter_2_add_date);
						$stmt3->bindParam("gifter_id", $gifter_id);
						$stmt3->bindParam("split_id", $split_id); 
						if($stmt3->execute()){
							return true;
						}else{
							return false;
						}
					}elseif(trim($gifter_3) == 0){ 
						//Free Slot Exist 
						//Update Table Split
						$stmt3 = $this->conn->prepare("UPDATE table_splits SET gifter_3 = :gifter_id, gifter_3_add_date = :gifter_3_add_date WHERE id = :split_id");
						$myTimeZone = $this->getUserDetailsById($gifter_id, "country_code");
						$date = new DateTime("now", new DateTimeZone($this->getTimeZoneByCountryCode($myTimeZone)) );
						$gifter_3_add_date = $date->format("Y-m-d H:i:s");
						$stmt3->bindParam("gifter_3_add_date", $gifter_3_add_date);
						$stmt3->bindParam("gifter_id", $gifter_id);
						$stmt3->bindParam("split_id", $split_id); 
						if($stmt3->execute()){
							return true;
						}else{
							return false;
						}
					}elseif(trim($gifter_4) == 0){ 
						//Free Slot Exist 
						//Update Table Split
						$stmt3 = $this->conn->prepare("UPDATE table_splits SET gifter_4 = :gifter_id, gifter_4_add_date = :gifter_4_add_date WHERE id = :split_id");
						$myTimeZone = $this->getUserDetailsById($gifter_id, "country_code");
						$date = new DateTime("now", new DateTimeZone($this->getTimeZoneByCountryCode($myTimeZone)) );
						$gifter_4_add_date = $date->format("Y-m-d H:i:s");
						$stmt3->bindParam("gifter_4_add_date", $gifter_4_add_date);
						$stmt3->bindParam("gifter_id", $gifter_id);
						$stmt3->bindParam("split_id", $split_id); 
						if($stmt3->execute()){
							return true;
						}else{
							return false;
						}
					}else{
						//Table Filled
						//Find Another table of same Level
						$alt_split_id = $this->getAltTableSplit($split_id);
						//Recurse addGifter
						if($alt_split_id != false){
							return $this->addGifterAlt($gifter_id, $alt_split_id);
						}
					}
				}else{
					return false;
				}	
			}else{
				return false;
			} 
		}
		function addGifterAlt($gifter_id, $split_id){
			if (!$this->isGifterBannedFromTable($gifter_id, $split_id)) {
				$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE id = :split_id");
				$stmt->bindParam("split_id", $split_id); 
				$stmt->execute();
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$gifter_1 = $row['gifter_1'];
				$gifter_2 = $row['gifter_2'];
				$gifter_3 = $row['gifter_3'];
				$gifter_4 = $row['gifter_4'];
				$free_gifter_slots = [];
				if($gifter_1 == 0){ 
					array_push($free_gifter_slots, "gifter_1");
				}
				if($gifter_2 == 0){ 
					array_push($free_gifter_slots, "gifter_2");
				}
				if($gifter_3 == 0){ 
					array_push($free_gifter_slots, "gifter_3");
				}
				if($gifter_4 == 0){ 
					array_push($free_gifter_slots, "gifter_4");
				}
				if (count($free_gifter_slots)>0) {
					//Free Slot Exist
					$slots = count($free_gifter_slots)-1;
					$selected_slot = $free_gifter_slots[round(rand(0, $slots))];
					$selected_slot_add_date = $selected_slot."_add_date";
					//Update Table Split
					$stmt3 = $this->conn->prepare("UPDATE table_splits SET $selected_slot = :gifter_id, $selected_slot_add_date = :gifter_add_date WHERE id = :split_id");
					$myTimeZone = $this->getUserDetailsById($gifter_id, "country_code");
					$date = new DateTime("now", new DateTimeZone($this->getTimeZoneByCountryCode($myTimeZone)) );
					$selected_slot_add_date_date = $date->format("Y-m-d H:i:s"); 
					$stmt3->bindParam("gifter_add_date", $selected_slot_add_date_date);
					$stmt3->bindParam("gifter_id", $gifter_id);
					$stmt3->bindParam("split_id", $split_id); 
					if($stmt3->execute()){
						return true;
					}else{
						return false;
					}
				}else{
					//Table Filled
					//Find Another table of same Level
					$alt_split_id = $this->getAltTableSplit($split_id);
					//Recurse addGifter
					if($alt_split_id != false){
						$this->addGifterAlt($gifter_id, $alt_split_id);
					}
				}
			}else{
				return false;
			}
		}
		function isActiveInTable($user_id, $table_id){
			$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE table_id = :table_id AND (gifter_1 = :gifter_1 OR gifter_2 = :gifter_2 OR gifter_3 = :gifter_3 OR gifter_4 = :gifter_4 OR builder_1 = :builder_1 OR builder_2 = :builder_2 OR legend = :legend_id)");
			$stmt->bindParam("table_id", $table_id); 
			$stmt->bindParam("builder_1", $user_id); 
			$stmt->bindParam("builder_2", $user_id);
			$stmt->bindParam("gifter_1", $user_id);
			$stmt->bindParam("gifter_2", $user_id);
			$stmt->bindParam("gifter_3", $user_id); 
			$stmt->bindParam("gifter_4", $user_id);
			$stmt->bindParam("legend_id", $user_id);  
			$stmt->execute();
			if ($stmt->rowCount()>0) {
				return true;
			}else{
				return false;
			}
		}
		function getAltTableSplit($split_id){
			$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE id = :split_id");
			$stmt->bindParam("split_id", $split_id); 
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$table_id = $row['table_id'];

			$stmt2 = $this->conn->prepare("SELECT id FROM table_splits WHERE table_id = :table_id AND id != :split_id AND (gifter_1 = :empty_gifter_1 OR gifter_2 = :empty_gifter_2 OR gifter_3 = :empty_gifter_3 OR gifter_4 = :empty_gifter_4) LIMIT 1");
			$empty_gifter_1 = 0;
			$empty_gifter_2 = 0;
			$empty_gifter_3 = 0;
			$empty_gifter_4 = 0;
			$stmt2->bindParam("table_id", $table_id); 
			$stmt2->bindParam("empty_gifter_1", $empty_gifter_1); 
			$stmt2->bindParam("empty_gifter_2", $empty_gifter_2); 
			$stmt2->bindParam("empty_gifter_3", $empty_gifter_3); 
			$stmt2->bindParam("empty_gifter_4", $empty_gifter_4);  
			$stmt2->bindParam("split_id", $split_id);
			$stmt2->execute();
			if ($stmt2->rowCount()>0) {
				//Alt Exists
				$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
				return $row2['id'];
			}else{
				return false;
			}
		}
		// function addGifterToTable1($gifter_id){
		// 	$stmt3 = $this->conn->prepare("SELECT id FROM table_splits WHERE table_id = :table_id LIMIT 1");
		// 		$table_id = 1;
		// 		$stmt3->bindParam("table_id", $table_id); 
		// 		$row = $stmt3->fetch(PDO::FETCH_ASSOC);
		// 		$split_id = $row['id'];
		// 		return $split_id;
		// 		//$this->addGifter($gifter_id, $split_id);
		// }
		function getFirstActiveTable($user_id){
			$stmt = $this->conn->prepare("SELECT id FROM table_splits WHERE legend = :legend_id OR builder_1 = :builder_1_id OR builder_2 = :builder_2_id OR gifter_1 = :gifter_1_id OR gifter_2 = :gifter_2_id OR gifter_3 = :gifter_3_id OR gifter_4 = :gifter_4_id ORDER BY id ASC LIMIT 1");
			$stmt->bindParam("legend_id", $user_id);
			$stmt->bindParam("builder_1_id", $user_id);
			$stmt->bindParam("builder_2_id", $user_id);
			$stmt->bindParam("gifter_1_id", $user_id);
			$stmt->bindParam("gifter_2_id", $user_id);
			$stmt->bindParam("gifter_3_id", $user_id); 
			$stmt->bindParam("gifter_4_id", $user_id); 
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row['id'];
		}
		function getUserActiveTables($user_id){
			$stmt = $this->conn->prepare("SELECT id FROM table_splits WHERE legend = :legend_id OR builder_1 = :builder_1_id OR builder_2 = :builder_2_id OR gifter_1 = :gifter_1_id OR gifter_2 = :gifter_2_id OR gifter_3 = :gifter_3_id OR gifter_4 = :gifter_4_id");
			$stmt->bindParam("legend_id", $user_id);
			$stmt->bindParam("builder_1_id", $user_id);
			$stmt->bindParam("builder_2_id", $user_id);
			$stmt->bindParam("gifter_1_id", $user_id);
			$stmt->bindParam("gifter_2_id", $user_id);
			$stmt->bindParam("gifter_3_id", $user_id); 
			$stmt->bindParam("gifter_4_id", $user_id); 
			$stmt->execute();
			$availableTables = "";
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				 $availableTables .= $row['id'].",";	
			}
			return $availableTables;
		}
		function getTableSplitDetailById($id, $detail){
			$stmt = $this->conn->prepare("SELECT $detail FROM table_splits WHERE id = :id");
			$stmt->bindParam("id", $id); 
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row[$detail];
		}
		function getTimeZoneByCountryCode($country_code){
			$stmt = $this->conn->prepare("SELECT zone_name FROM time_zone WHERE country_code = :country_code LIMIT 1");
			$stmt->bindParam("country_code", $country_code); 
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row['zone_name'];
		}
		function converToTz($time="",$toTz='',$fromTz='')
	    {   
	        //timezone by php friendly values
	        $date = new DateTime($time, new DateTimeZone($fromTz));
	        $date->setTimezone(new DateTimeZone($toTz));
	        $time= $date->format('Y-m-d H:i:s');
	        return $time;	 
	    }
		function avaibaleTableSlot($table_id, $user_id){
			$stmt = $this->conn->prepare("SELECT * FROM table_splits WHERE table_id = :table_id AND legend != :legend AND builder_1 != :builder_1 AND builder_2 != :builder_2 AND gifter_1 != :gifter_1 AND gifter_2 != :gifter_2 AND gifter_3 != :gifter_3 AND gifter_4 != :gifter_4 AND (gifter_1 = :empty_gifter_1 OR gifter_2 = :empty_gifter_2 OR gifter_3 = :empty_gifter_3 OR gifter_4 = :empty_gifter_4)");
			$empty_gifter_1 = 0;
			$empty_gifter_2 = 0;
			$empty_gifter_3 = 0;
			$empty_gifter_4 = 0;
			$stmt->bindParam("table_id", $table_id);
			$stmt->bindParam("empty_gifter_1", $empty_gifter_1);
			$stmt->bindParam("empty_gifter_2", $empty_gifter_2);
			$stmt->bindParam("empty_gifter_3", $empty_gifter_3);
			$stmt->bindParam("empty_gifter_4", $empty_gifter_4);
			$stmt->bindParam("legend", $user_id);
			$stmt->bindParam("builder_1", $user_id);
			$stmt->bindParam("builder_2", $user_id);
			$stmt->bindParam("gifter_1", $user_id);
			$stmt->bindParam("gifter_2", $user_id);
			$stmt->bindParam("gifter_3", $user_id);
			$stmt->bindParam("gifter_4", $user_id);   
			$stmt->execute();
			$empty_rows = 0;
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				$split_id = $row['id'];
				$gifter_1 = $row['gifter_1'];
				$gifter_2 = $row['gifter_2'];
				$gifter_3 = $row['gifter_3'];
				$gifter_4 = $row['gifter_4'];  
				if ($gifter_1 == 0) {
					$empty_rows = $empty_rows+1;
				}
				if ($gifter_2 == 0) {
					$empty_rows = $empty_rows+1;
				}
				if ($gifter_3 == 0) {
					$empty_rows = $empty_rows+1;
				}
				if ($gifter_4 == 0) {
					$empty_rows = $empty_rows+1;
				}
			}
			return $empty_rows;
		}
		function getTableDetailById($id, $detail){
			$stmt = $this->conn->prepare("SELECT $detail FROM tables WHERE id = :id");
			$stmt->bindParam("id", $id); 
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row[$detail];
		}
		function getSiteSettings($variable){
			$stmt = $this->conn->prepare("SELECT * FROM settings WHERE variable = :variable LIMIT 1");
			$stmt->bindParam("variable", $variable); 
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return trim($row['value']);
		}
		function getTotalUserVotes($candidate_id){
			$stmtt = $this->conn->prepare("SELECT SUM(num_votes) AS total_votes FROM votes WHERE candidate_id = :candidate_id AND status != :pending");
			$stmtt->bindParam("candidate_id", $candidate_id);
			$pending = "pending";
			$stmtt->bindParam("pending", $pending);
			$stmtt->execute();		
			$row = $stmtt->fetch(PDO::FETCH_ASSOC);
			

			$total_votes = $row['total_votes'];
			if ($total_votes == 0) {
				return 0;
			}else{
				return $total_votes;
			}
		} 
		function getParishionerDetails($return_data, $where_field, $data){
			$stmt = $this->conn->prepare("SELECT * FROM parishioners WHERE $where_field = :data LIMIT 1");
			$stmt->bindParam("data", $data); 
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row[$return_data];
		}
		function getParishionerFullNameByPrn($prn){
			$stmt = $this->conn->prepare("SELECT f_name, l_name FROM parishioners WHERE prn = :prn LIMIT 1");
			$stmt->bindParam("prn", $prn); 
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row['f_name']." ".$row['l_name'];
		}
		function getBdData($return_data,$table, $where_field, $data){
			$stmt = $this->conn->prepare("SELECT * FROM $table WHERE $where_field = :data LIMIT 1");
			$stmt->bindParam("data", $data); 
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row[$return_data];
		} 
		function saveTag($tag_id, $user_id){
			$stmt = $this->conn->prepare("SELECT * FROM parishioner_tags WHERE tag_id = :tag_id AND user_id = :user_id");
			$stmt->bindParam("tag_id", $tag_id); 
			$stmt->bindParam("user_id", $user_id); 
			$stmt->execute();
			if ($stmt->rowCount()>0) {
				//Tag Already exits
			}else{
				$stmt1 = $this->conn->prepare("INSERT INTO parishioner_tags (tag_id, user_id) VALUES(:tag_id, :user_id)");
				$stmt1->bindParam("tag_id", $tag_id); 
				$stmt1->bindParam("user_id", $user_id); 
				$stmt1->execute();
			}
		}
		function getAdminDetailsByEmail($email, $data){
			$stmt = $this->conn->prepare("SELECT * FROM admins WHERE email = :email LIMIT 1");
			$stmt->bindParam("email", $email); 
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row[$data];
		}
		function getAdminDetailsById($id, $data){
			$stmt = $this->conn->prepare("SELECT * FROM admins WHERE id = :id LIMIT 1");
			$stmt->bindParam("id", $id); 
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row[$data];
		}
		function getCandidateDetailsByEmail($email, $campaign_slug, $data){
			$stmt = $this->conn->prepare("SELECT * FROM candidates WHERE email = :email AND campaign_slug = :campaign_slug LIMIT 1");
			$stmt->bindParam("email", $email);
			$stmt->bindParam("campaign_slug", $campaign_slug); 
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row[$data];
		}
		function updateSettings($param, $data){
			$stmt = $this->conn->prepare("UPDATE settings SET value = :data WHERE variable = :param");
			$stmt->bindParam("data", $data); 
			$stmt->bindParam("param", $param); 
			if($stmt->execute()){
				return true;
			}else{
				return false;
			} 
		}
		function getCandidateDetailsById($id, $data){
			$stmt = $this->conn->prepare("SELECT * FROM candidates WHERE id = :id LIMIT 1");
			$stmt->bindParam("id", $id); 
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row[$data];
		}
		function getAdminDetail($data, $where, $where_value){
			$stmt = $this->conn->prepare("SELECT $data FROM admins WHERE $where = :where_value LIMIT 1");
			$stmt->bindParam("where_value", $where_value); 
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row[$data];
		}
		function getAdminNameById($id){
			$stmt = $this->conn->prepare("SELECT * FROM admins WHERE id = :id LIMIT 1");
			$stmt->bindParam("id", $id); 
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row["f_name"]." ".$row["l_name"];
		}
		
		function getUserDetailsByEmail($email, $data){
			$stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
			$stmt->bindParam("email", $email); 
			$stmt->execute();
			if ($stmt->rowCount()>0) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				return $row[$data];
			}else{
				return "";
			} 
		}
		function getUserDetailsById($id, $data){
			$stmt = $this->conn->prepare("SELECT $data FROM users WHERE id = :id");
			$stmt->bindParam("id", $id); 
			$stmt->execute();
			if ($stmt->rowCount()>0) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				return $row[$data];
			}else{
				return "";
			} 
		}
		
		function send_mail($mail, $subject, $from, $to, $message, $cc = ""){
			// include("email-template-1.php");
			if ($from != $to) {
				$unsubscriber_email = "?email=".$to;
			}else{
				$unsubscriber_email = "";
			}
			$gmail_app_password = $this->getSiteSettings("gmail_app_password");
			$site_name = $this->getSiteSettings("site_name");

			$htmlContent = '<!doctype html>
			<html>
			<head>
			<meta name="viewport" content="width=device-width">
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<title>'.$subject.'</title>
			<style>
			/* -------------------------------------
			INLINED WITH htmlemail.io/inline
			------------------------------------- */
			/* -------------------------------------
			RESPONSIVE AND MOBILE FRIENDLY STYLES
			------------------------------------- */
			@media only screen and (max-width: 620px) {
				table[class=body] h1 {
					font-size: 28px !important;
					margin-bottom: 10px !important;
				}
				table[class=body] p,
				table[class=body] ul,
				table[class=body] ol,
				table[class=body] td,
				table[class=body] span,
				table[class=body] a {
					font-size: 16px !important;
				}
				table[class=body] .wrapper,
				table[class=body] .article {
					padding: 10px !important;
				}
				table[class=body] .content {
					padding: 0 !important;
				}
				table[class=body] .container {
					padding: 0 !important;
					width: 100% !important;
				}
				table[class=body] .main {
					border-left-width: 0 !important;
					border-radius: 0 !important;
					border-right-width: 0 !important;
				}
				table[class=body] .btn table {
					width: 100% !important;
				}
				table[class=body] .btn a {
					width: 100% !important;
				}
				table[class=body] .img-responsive {
					height: auto !important;
					max-width: 100% !important;
					width: auto !important;
				}
			}

			/* -------------------------------------
			PRESERVE THESE STYLES IN THE HEAD
			------------------------------------- */
			@media all {
				.ExternalClass {
					width: 100%;
				}
				.ExternalClass,
				.ExternalClass p,
				.ExternalClass span,
				.ExternalClass font,
				.ExternalClass td,
				.ExternalClass div {
					line-height: 100%;
				}
				.apple-link a {
					color: inherit !important;
					font-family: inherit !important;
					font-size: inherit !important;
					font-weight: inherit !important;
					line-height: inherit !important;
					text-decoration: none !important;
				}
	      #MessageViewBody a {
					color: inherit;
					text-decoration: none;
					font-size: inherit;
					font-family: inherit;
					font-weight: inherit;
					line-height: inherit;
				}
				.btn-primary table td:hover {
					background-color: #34495e !important;
				}
				.btn-primary a:hover {
					background-color: #34495e !important;
					border-color: #34495e !important;
				}
			}
			</style>
			</head>
			<body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
			<!-- <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">'.$this->get_words($message, "120").'</span> -->
			<table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">
			<tr>
			<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
			<td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">
			<div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">

			<!-- START CENTERED WHITE CONTAINER -->
			<table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">

			<!-- START MAIN CONTENT AREA -->
			<tr>
			<td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">
			'.nl2br($message).'
			</td>
			</tr>

			<!-- END MAIN CONTENT AREA -->
			</table>

			<!-- START FOOTER -->
			<div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">
			<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
			<tr>
			<td class="content-block" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">
			<div class="apple-link" style="color: #999999; font-size: 12px; text-align: center;">'.$this->getSiteSettings("site_name").', '.$this->getSiteSettings("address") .'</div>
			</td>
			</tr>
			</table>
			</div>
			<!-- END FOOTER -->

			<!-- END CENTERED WHITE CONTAINER -->
			</div>
			</td>
			<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
			</tr>
			</table>
			</body>
			</html>';
			

			try {
			    //Server settings
			    //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
			    $mail->isSMTP();                                            //Send using SMTP
			    $mail->Host = 'smtp.gmail.com';                     //Set the SMTP server to send through
			    $mail->SMTPAuth = true;         
			    $mail->SMTPSecure= 'tls';
			    $mail->Port = 587;
			    //Enable SMTP authentication
			    $mail->Username   = $from;                     //SMTP username
			    $mail->Password   = $gmail_app_password;                               //SMTP password
			    // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

			    //Recipients
			    $mail->setFrom($from, $site_name);
			    // $mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
			    $mail->addAddress($to);               //Name is optional
			    $mail->addReplyTo($from);
			    if ($cc != "") {
			    	$bccemails = explode(",", $cc);
			    	for ($i=0; $i < count($bccemails); $i++) {
				    	if (trim($bccemails[$i]) != "") {
				    		$mail->addBCC($bccemails[$i]);	
				    	}
			    	}
			    	
			    }
			    
			    // $mail->addBCC('bcc@example.com');

			    //Attachments
			    // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
			    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

			    //Content
			    $body = $htmlContent;
			    $mail->isHTML(true);                                  //Set email format to HTML
			    $mail->Subject = $subject;
			    $mail->Body    = $body;
			    $mail->AltBody = strip_tags($body);
			 
			    if(!$mail->Send()) {
			      return "Error while sending Email.";
			    } else {
			      return "Success";
			    } 
			} catch (Exception $e) {
			    return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
			}
		}
		// function send_mail($subject, $from, $to, $message){
		// 	// include("email-template-1.php");
		// 	if ($from != $to) {
		// 		$unsubscriber_email = "?email=".$to;
		// 	}else{
		// 		$unsubscriber_email = "";
		// 	}
		// 	$htmlContent = '<!doctype html>
		// 	<html>
		// 	<head>
		// 	<meta name="viewport" content="width=device-width">
		// 	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		// 	<title>'.$subject.'</title>
		// 	<style>
		// 	/* -------------------------------------
		// 	INLINED WITH htmlemail.io/inline
		// 	------------------------------------- */
		// 	/* -------------------------------------
		// 	RESPONSIVE AND MOBILE FRIENDLY STYLES
		// 	------------------------------------- */
		// 	@media only screen and (max-width: 620px) {
		// 		table[class=body] h1 {
		// 			font-size: 28px !important;
		// 			margin-bottom: 10px !important;
		// 		}
		// 		table[class=body] p,
		// 		table[class=body] ul,
		// 		table[class=body] ol,
		// 		table[class=body] td,
		// 		table[class=body] span,
		// 		table[class=body] a {
		// 			font-size: 16px !important;
		// 		}
		// 		table[class=body] .wrapper,
		// 		table[class=body] .article {
		// 			padding: 10px !important;
		// 		}
		// 		table[class=body] .content {
		// 			padding: 0 !important;
		// 		}
		// 		table[class=body] .container {
		// 			padding: 0 !important;
		// 			width: 100% !important;
		// 		}
		// 		table[class=body] .main {
		// 			border-left-width: 0 !important;
		// 			border-radius: 0 !important;
		// 			border-right-width: 0 !important;
		// 		}
		// 		table[class=body] .btn table {
		// 			width: 100% !important;
		// 		}
		// 		table[class=body] .btn a {
		// 			width: 100% !important;
		// 		}
		// 		table[class=body] .img-responsive {
		// 			height: auto !important;
		// 			max-width: 100% !important;
		// 			width: auto !important;
		// 		}
		// 	}

		// 	/* -------------------------------------
		// 	PRESERVE THESE STYLES IN THE HEAD
		// 	------------------------------------- */
		// 	@media all {
		// 		.ExternalClass {
		// 			width: 100%;
		// 		}
		// 		.ExternalClass,
		// 		.ExternalClass p,
		// 		.ExternalClass span,
		// 		.ExternalClass font,
		// 		.ExternalClass td,
		// 		.ExternalClass div {
		// 			line-height: 100%;
		// 		}
		// 		.apple-link a {
		// 			color: inherit !important;
		// 			font-family: inherit !important;
		// 			font-size: inherit !important;
		// 			font-weight: inherit !important;
		// 			line-height: inherit !important;
		// 			text-decoration: none !important;
		// 		}
  //     #MessageViewBody a {
		// 		color: inherit;
		// 		text-decoration: none;
		// 		font-size: inherit;
		// 		font-family: inherit;
		// 		font-weight: inherit;
		// 		line-height: inherit;
		// 	}
		// 	.btn-primary table td:hover {
		// 		background-color: #34495e !important;
		// 	}
		// 	.btn-primary a:hover {
		// 		background-color: #34495e !important;
		// 		border-color: #34495e !important;
		// 	}
		// }
		// </style>
		// </head>
		// <body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
		// <!-- <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">'.$this->get_words($message, "120").'</span> -->
		// <table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">
		// <tr>
		// <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
		// <td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">
		// <div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">

		// <!-- START CENTERED WHITE CONTAINER -->
		// <table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">

		// <!-- START MAIN CONTENT AREA -->
		// <tr>
		// <td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">
		// '.nl2br($message).'
		// </td>
		// </tr>

		// <!-- END MAIN CONTENT AREA -->
		// </table>

		// <!-- START FOOTER -->
		// <div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">
		// <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
		// <tr>
		// <td class="content-block" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">
		// <span class="apple-link" style="color: #999999; font-size: 12px; text-align: center;">'.$this->getSiteSettings("site_name").', '.$this->getSiteSettings("address") .'</span>
		// <br> Don\'t like these emails? <a href="'.$this->getSiteUrl().'/unsubscribe'.$unsubscriber_email.'" style="text-decoration: underline; color: #999999; font-size: 12px; text-align: center;">Unsubscribe</a>.
		// </td>
		// </tr>
		// </table>
		// </div>
		// <!-- END FOOTER -->

		// <!-- END CENTERED WHITE CONTAINER -->
		// </div>
		// </td>
		// <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
		// </tr>
		// </table>
		// </body>
		// </html>';
			
		// 	// Set content-type header for sending HTML email
		// 	$headers = "MIME-Version: 1.0" . "\r\n";
		// 	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

  //           // Additional headers
		// 	$headers .= 'From: '.substr($this->getSiteSettings("site_name"), 0, 20).'<'.$from.'>' . "\r\n";

  //               // Send email            
		// 	if(mail($to, $subject, $htmlContent, $headers)){
		// 		return true;
		// 	}else{
		// 		return false;
		// 	}
		// }
		function updateAdmin($field, $data, $selector, $selector_value){
			$stmt = $this->conn->prepare("UPDATE admins SET $field = :data WHERE $selector = :selector_value");
			$stmt->bindParam("data", $data); 
			$stmt->bindParam("selector_value", $selector_value); 
			if($stmt->execute()){
				return true;
			}
		}
		
		function external_link($link){
		    if (strpos($link, "https://") == false AND strpos($link, "http://") == false) {
		      $link = str_replace("http://", "", $link);
		      $link = str_replace("https://", "", $link);
		      $link = "http://".$link;
		      return $link;
		    }else{
		      return $link;
		    }  
		 }
		function generate2FCode($max){
			$alpha = array('a','e','i','o','u','b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','y','z');
			$numeric = array('1','2','3','4','5','6','7','8','9','0');
			$code = '';
			$max = $max/2;
			for ($i=0; $i < $max; $i++) { 
				$alCount = count($alpha)-1;
				$numCount = count($numeric)-1;
				$alphX = $alpha[round(rand(0,$alCount))];
				$numX = $numeric[round(rand(0,$numCount))];

				$switch = round(rand(2,10));
				if ($switch%2 == 0) {
		                //Even Number
					$code .= $alphX.$numX;
				}else{
		                //Odd
					$code .= $numX.$alphX;
				}

			}
			return $code;
		}
		function generateCode($max){
			$alpha = array('a','e','i','o','u','b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','y','z');
			$numeric = array('1','2','3','4','5','6','7','8','9','0');
			$code = '';
			$max = $max/2;
			for ($i=0; $i < $max; $i++) { 
				$alCount = count($alpha)-1;
				$numCount = count($numeric)-1;
				$alphX = $alpha[round(rand(0,$alCount))];
				$numX = $numeric[round(rand(0,$numCount))];

				$switch = round(rand(2,10));
				if ($switch%2 == 0) {
		                //Even Number
					$code .= $alphX.$numX;
				}else{
		                //Odd
					$code .= $numX.$alphX;
				}

			}
			return $code;
		}
		function url_origin( $s, $use_forwarded_host = false )
		{
		    $ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
		    $sp       = strtolower( $s['SERVER_PROTOCOL'] );
		    $protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
		    $port     = $s['SERVER_PORT'];
		    $port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
		    $host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
		    $host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
		    return $protocol . '://' . $host;
		}

		function full_url( $s, $use_forwarded_host = false )
		{
		    return $this->url_origin( $s, $use_forwarded_host ) . $s['REQUEST_URI'];
		}

		function TwoFAuthenticate($email){
			$TwoFCode = $this->generate2FCode(6);
			$first_name = $this->getAdminDetailsByEmail($email, "f_name");
			$message = "Hi ".$first_name."<br>We noticed a login to your account. To verify your account is safe, please use the following code to verify your login — it will expire in 15 minutes:<br><h2>".$TwoFCode."</h2>";
			if($this->send_mail("2FA Confirmation Code", $this->getSiteSettings("email"), $email, $message)){
				//Save 2FA to db
				if($this->updateAdmin("2fa_code", $TwoFCode,"email", $email)){
					//Update 2fa_expiry date
					$_2fa_expiry = date("Y-m-d H:i:s", strtotime("+ 15 minute"));
					$this->updateAdmin("2fa_expiry", $_2fa_expiry,"email", $email);
					return true;
				}

			}

		}
		function asterisks_email($email){
			$minFill = 8;
			return preg_replace_callback(
				'/^(.)(.*?)([^@]?)(?=@[^@]+$)/u',
				function ($m) use ($minFill) {
					return $m[1]
					. str_repeat("*", max($minFill, mb_strlen($m[2], 'UTF-8')))
					. ($m[3] ?: $m[1]);
				},
				$email
			);
		} 
		function generateUniqueCode($max, $table, $field){ 
			$alpha = array('a','e','i','o','u','b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','y','z');
			$numeric = array('1','2','3','4','5','6','7','8','9','0');
			$code = '';
			$max = $max/2;
			for ($i=0; $i <= $max; $i++) { 
				$alCount = count($alpha)-1;
				$numCount = count($numeric)-1;
				$alphX = $alpha[round(rand(0,$alCount))];
				$numX = $numeric[round(rand(0,$numCount))];

				$switch = round(rand(2,10));
				if ($switch%2 == 0) {
                //Even Number
					$code .= strtoupper($alphX.$numX);
				}else{
                //Odd
					$code .= strtoupper($numX.$alphX);
				}

			}
        	//Check if Code already exist
			$stmt = $this->conn->prepare("SELECT $field FROM $table WHERE $field = :code");
			$stmt->bindParam("code", $code);
			$stmt->execute();
			if ($stmt->rowCount()>0) {
        	//Code exist
        	//Regenerate
				$this->generateUniqueCode($max, $table, $field);
			}else{
				return $code;
			} 
		}
		
		function compress($source, $destination, $quality) {
			$file_size = filesize($source);
			$file_kb_size = $file_size/1024;
			if($file_kb_size >= 80 && $file_kb_size < 150){
				$quality = 25;
			}elseif ($file_kb_size >= 150 && $file_kb_size < 350) {
				$quality = 22;
			}elseif ($file_kb_size >= 350 && $file_kb_size < 800) {
				$quality = 21;
			}elseif ($file_kb_size >= 800 && $file_kb_size < 1200) {
				$quality = 18;
			}elseif ($file_kb_size >= 1200 && $file_kb_size < 1600) {
				$quality = 15;
			}elseif ($file_kb_size >= 1600 && $file_kb_size < 2200) {
				$quality = 10;
			}elseif ($file_kb_size >= 2200 && $file_kb_size < 2600) {
				$quality = 8;
			}elseif ($file_kb_size >= 2600) {
				$quality = 7;
			}
			$info = getimagesize($source);
			if ($info['mime'] == 'image/jpeg') 
				$image = imagecreatefromjpeg($source);

			elseif ($info['mime'] == 'image/gif') 
				$image = imagecreatefromgif($source);

			elseif ($info['mime'] == 'image/png') 
				$image = imagecreatefrompng($source);

			if(imagejpeg($image, $destination, $quality)){
				return true;
			}else{
				return false;
			}
		}
		function slugify($str){
            # special accents
			$a = array('À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ','A','a','A','a','A','a','C','c','C','c','C','c','C','c','D','d','Ð','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','?','?','J','j','K','k','L','l','L','l','L','l','?','?','L','l','N','n','N','n','N','n','?','O','o','O','o','O','o','Œ','œ','R','r','R','r','R','r','S','s','S','s','S','s','Š','š','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Ÿ','Z','z','Z','z','Ž','ž','?','ƒ','O','o','U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U','u','?','?','?','?','?','?');
			$b = array('A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','s','a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y','A','a','A','a','A','a','C','c','C','c','C','c','C','c','D','d','D','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','IJ','ij','J','j','K','k','L','l','L','l','L','l','L','l','l','l','N','n','N','n','N','n','n','O','o','O','o','O','o','OE','oe','R','r','R','r','R','r','S','s','S','s','S','s','S','s','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Y','Z','z','Z','z','Z','z','s','f','O','o','U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U','u','A','a','AE','ae','O','o');
			return strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/','/[ -]+/','/^-|-$/'),array('','-',''),str_replace($a,$b,$str)));
		}
		function uniqueSlugify($str,$table,$filed){ 
            # special accents
			$a = array('À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ','A','a','A','a','A','a','C','c','C','c','C','c','C','c','D','d','Ð','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','?','?','J','j','K','k','L','l','L','l','L','l','?','?','L','l','N','n','N','n','N','n','?','O','o','O','o','O','o','Œ','œ','R','r','R','r','R','r','S','s','S','s','S','s','Š','š','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Ÿ','Z','z','Z','z','Ž','ž','?','ƒ','O','o','U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U','u','?','?','?','?','?','?');
			$b = array('A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','s','a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y','A','a','A','a','A','a','C','c','C','c','C','c','C','c','D','d','D','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','IJ','ij','J','j','K','k','L','l','L','l','L','l','L','l','l','l','N','n','N','n','N','n','n','O','o','O','o','O','o','OE','oe','R','r','R','r','R','r','S','s','S','s','S','s','S','s','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Y','Z','z','Z','z','Z','z','s','f','O','o','U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U','u','A','a','AE','ae','O','o');
			$slug = strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/','/[ -]+/','/^-|-$/'),array('','-',''),str_replace($a,$b,$str))); 
			$result = $this->conn->prepare("SELECT COUNT(*) AS NumHits FROM $table WHERE  $filed  = :slug");
			$result->bindParam("slug", $slug);
			$result->execute();
			$row = $result->fetch(PDO::FETCH_ASSOC);
			$numHits = $row['NumHits'];

			return ($numHits > 0) ? ($slug . '-' . $numHits) : $slug;
		}  
		function get_words($word, $count) {
			preg_match("/(?:\w+(?:\W+|$)){0,$count}/", $word, $matches);
			if (str_word_count($word)>$count) {
				$append_dot = "...";
			}else{
				$append_dot = '';
			}
			return $matches[0].$append_dot;
		}
		function getExtension($str) {
			$strr = stripslashes($str);
			$i = strrpos($strr,".");
			if (!$i) { return ""; } 

			$l = strlen($str) - $i;
			$ext = strtolower(substr($str,$i+1,$l));
			return $ext;
		}
		 
		function getSiteUrl(){
			$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
			return $actual_link;
		}
		function getSiteFullUrl(){
			$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			return $actual_link;
		}
		function cropImage($target_url, $uploadedfile, $thumb_width, $thumb_height) {
			$info = getimagesize($target_url);
			if ($info['mime'] == 'image/jpeg') 
				$image = imagecreatefromjpeg($target_url);

			elseif ($info['mime'] == 'image/gif') 
				$image = imagecreatefromgif($target_url);

			elseif ($info['mime'] == 'image/png') 
				$image = imagecreatefrompng($target_url);
			
			// $extension = $this->getExtension($target_url);
			// if($extension=="jpg" || $extension=="jpeg" ){ 
			// 	$image = imagecreatefromjpeg($uploadedfile);
			// }else if($extension=="png"){ 
			// 	$image = imagecreatefrompng($uploadedfile);
			// }else{
			// 	$image = imagecreatefromgif($uploadedfile);
			// }
			$filename = $target_url;
			$width = imagesx($image);
			$height = imagesy($image);
		  //$image_type = imagetypes($image); //IMG_GIF | IMG_JPG | IMG_PNG | IMG_WBMP | IMG_XPM

		  // if($width==$height) {

		  // 	$thumb_width = $width;
		  // 	$thumb_height = $height;

		  // } elseif($width<$height) {

		  // 	$thumb_width = $width;
		  // 	$thumb_height = $width;

		  // } elseif($width>$height) {

		  // 	$thumb_width = $height;
		  // 	$thumb_height = $height;

		  // } else {
		  // 	$thumb_width = 150;
		  // 	$thumb_height = 150;
		  // }

			$original_aspect = $width / $height;
			$thumb_aspect = $thumb_width / $thumb_height;

			if ( $original_aspect >= $thumb_aspect ) {

		     // If image is wider than thumbnail (in aspect ratio sense)
				$new_height = $thumb_height;
				$new_width = $width / ($height / $thumb_height);

			}
			else {
		     // If the thumbnail is wider than the image
				$new_width = $thumb_width;
				$new_height = $height / ($width / $thumb_width);
			}

			$thumb = imagecreatetruecolor( $thumb_width, $thumb_height );

		  // Resize and crop
			imagecopyresampled($thumb,
				$image,
		         0 - ($new_width - $thumb_width) / 2, // Center the image horizontally
		         0 - ($new_height - $thumb_height) / 2, // Center the image vertically
		         0, 0,
		         $new_width, $new_height,
		         $width, $height);
			imagejpeg($thumb, $filename, 80);
			// Clear Memory
			imagedestroy($thumb);
			return $filename;
		}
		function cropSocialImage($uploadedfile, $destination, $height, $width){
			$extension = $this->getExtension($uploadedfile);
			$image = imagecreatefromjpeg($uploadedfile);    
    // if($extension=="jpg" || $extension=="jpeg" ){ 
    //   $image = imagecreatefromjpeg($uploadedfile);
    //   }else if($extension=="png"){ 
    //       $image = imagecreatefrompng($uploadedfile);
    //   }else {
    //       $image = imagecreatefromgif($uploadedfile);
    //   }
			$filename = $destination;

			$thumb_width = $height;
			$thumb_height = $width;

			$width = imagesx($image);
			$height = imagesy($image);

			$original_aspect = $width / $height;
			$thumb_aspect = $thumb_width / $thumb_height;

			if ( $original_aspect >= $thumb_aspect ){
       // If image is wider than thumbnail (in aspect ratio sense)
				$new_height = $thumb_height;
				$new_width = $width / ($height / $thumb_height);
			}else{
       // If the thumbnail is wider than the image
				$new_width = $thumb_width;
				$new_height = $height / ($width / $thumb_width);
			}

			$thumb = imagecreatetruecolor( $thumb_width, $thumb_height );

    // Resize and crop
			imagecopyresampled($thumb,
				$image,
                       0 - ($new_width - $thumb_width) / 2, // Center the image horizontally
                       0 - ($new_height - $thumb_height) / 2, // Center the image vertically
                       0, 0,
                       $new_width, $new_height,
                       $width, $height);
			imagejpeg($thumb, $filename, 80);
			// Clear Memory
			imagedestroy($thumb);
		}

		 
		function get_stripped_words($str, $num){
		    return substr(strip_tags($str),0,$num);
		}
		function timeAgo($time_ago) { 
        $time_ago =  strtotime($time_ago) ? strtotime($time_ago) : $time_ago;
        $time  = time() - $time_ago;

        switch($time):
            // seconds
            case $time <= 60;
            return 'less than a minute ago';
            // minutes
            case $time >= 60 && $time < 3600;
            return (round($time/60) == 1) ? 'a minute' : round($time/60).' minutes ago';
            // hours
            case $time >= 3600 && $time < 86400;
            return (round($time/3600) == 1) ? 'a hour ago' : round($time/3600).' hours ago';
            // days
            case $time >= 86400 && $time < 604800;
            return (round($time/86400) == 1) ? 'a day ago' : round($time/86400).' days ago';
            // weeks
            case $time >= 604800 && $time < 2628000;
            return (round($time/604800) == 1) ? 'a week ago' : round($time/604800).' weeks ago';
            // months
            case $time >= 2628000 && $time < 31207680;
            return (round($time/2628000) == 1) ? 'a month ago' : round($time/2628000).' months ago';
            // years
            case $time >= 31547680;
            return (round($time/31547680) == 1) ? 'a year ago' : round($time/31547680).' years ago' ;

        endswitch;
    } 
    function get_ip_address() {
	    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
		    $address = $_SERVER['HTTP_CLIENT_IP'];
		  }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		    $address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		  }else{
		    $address = $_SERVER['REMOTE_ADDR'];
		  }
		  return $address;
  	}
  	 
}
?>