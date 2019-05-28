<?php
    
    require_once("common.php");

	class Database {
		
		private $pdo;
	
		function __construct() {
			
			$this->pdo = new PDO('mysql:host='. $GLOBALS['servername'] .';dbname='. $GLOBALS['dbname'], $GLOBALS['username'], $GLOBALS['password']);			
			$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
			$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		}
		
		function executeReader($query, $parameters = null, $single = false) {
			$status = false;
			$stmt = $this->pdo->prepare($query);				
			
			if(empty($parameters)) {
				$status = $stmt->execute();
			}
			else {
				$status = $stmt->execute($parameters);					
			}
			
			if ($status) {
				if ($single) {
					$resultSet = $stmt->fetch();
				}
				else {
					$resultSet = $stmt->fetchAll();
				}
				
				return [
					'status' => true,					
					'resultSet' => $resultSet
				];
			} else {
				return [
					'status' => false,					
					'message' => 'Error occured: Cannot fetch record',
					'errorCode' => json_encode($stmt->errorCode()),
					'errorInfo' => json_encode($stmt->errorInfo())
				];
			}
		}
		
		function executeProcedure($query, $resultSet, $parameters = null, $single = false) {
			try	{
				
				$index = 0;
				$query = 'CALL ' . $query;
				$stmt = $this->pdo->prepare($query);
				$status = false;
				
				if(empty($parameters)) {
					$status = $stmt->execute();
				}
				else {
					$status = $stmt->execute($parameters);
				}

				if ($status && $resultSet != null) {
					if ($single) {
						do {
							if($stmt->columnCount()) {
								$data = $stmt->fetch();
								$keys = array_keys($resultSet);			
								$resultSet[$keys[$index++]] = $data;
							}
						} while ($stmt->nextRowset());
					}
					else {
						do {
							if($stmt->columnCount()) {
								$data = $stmt->fetchAll();
								$keys = array_keys($resultSet);			
								$resultSet[$keys[$index++]] = $data;
							}
						} while ($stmt->nextRowset());
					}

					return [
						'status' => true,					
						'resultSet' => $resultSet
					];
				} else {
					return [
						'status' => false,					
						'message' => 'Error occured: Cannot fetch record',
						'errorCode' => json_encode($stmt->errorCode()),
						'errorInfo' => json_encode($stmt->errorInfo())
					];
				}
				
			}
			catch (PDOException $e) {
				return [
					'status' => false,
					'message' => $e->getMessage()					
				];
			}
		}

		function executeQuery($query, $parameters = null) {
			try	{
				
				$status = false;
				$stmt = $this->pdo->prepare($query);
				
				if(empty($parameters)) {
					$status = $stmt->execute();
				}
				else {
					$status = $stmt->execute($parameters);
				}
				
				if ($status) {
					$rowCount = $stmt->rowCount();
					return [
						'status' => true,					
						'count' => $rowCount,
						'message' => "Query executed successfully"
					];
				} else {
					return [
						'status' => false,					
						'message' => 'Error occured: Cannot fetch record',
						'errorCode' => json_encode($stmt->errorCode()),
						'errorInfo' => json_encode($stmt->errorInfo())
					];
				}
				
			}
			catch (PDOException $e) {
				return [
					'status' => false,
					'message' => $e->getMessage()					
				];
			}
		}
	}

?>