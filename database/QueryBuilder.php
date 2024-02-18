<?php
    class QueryBuilder
    {

        protected $pdo;

        public function __construct($pdo)
        {
            $this->pdo = $pdo;
        }

        public function selectAll( $table_name ){

            $statement = $this->pdo->prepare("select * from {$table_name}");

            $statement->execute();

            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        
            return $data;

        }

        public function selectWhere($table_name, $condition,$order = "asc",$multi = false, $columns){

            $first = $condition["first"];
            $operator = $condition["condition"];
            $second = $condition["second"];
            
            $columns = implode(",",$columns);

            $statement = $this->pdo->prepare("SELECT $columns from $table_name WHERE $first $operator:val ORDER BY $table_name.id $order");

            $statement->execute([ ":val" => $second]);
            
            if($multi)
                return $statement->fetchAll(PDO::FETCH_ASSOC);

            return $statement->fetch(PDO::FETCH_ASSOC);

        }
        
        public function delete($table_name,$unique){
            try{
                $statement = $this->pdo->prepare("
                        DELETE FROM $table_name 
                        WHERE id=:id"
                    );

                return $statement->execute([':id'=>$unique]); 
            }
            catch(PDOException $e){
                dd("$e");
            }
        }

        public function insert($table_name,$rows){

            try{
                $columns = implode(",",array_keys($rows));

                foreach($rows as $key=>$value){
                    $prepare_key[":$key"] = $value;
                }
                
                $keys = implode(",",array_keys($prepare_key));
                
                $statement = $this->pdo->prepare("
                        INSERT INTO $table_name($columns) 
                        values($keys)"
                    );

                return $statement->execute($rows);

            }
            
            catch(PDOException $e){
                dd("$e");
            }
        }

        public function raw($sql){

            return $this->pdo->query($sql);

        }

        public function update($table_name, $id, $row){
            try{

                foreach($row as $key=>$value){
                    $prepare_key["$key=:$key"] = $value;
                }
                
                $keys = implode(",",array_keys($prepare_key));

                $statement = $this->pdo->prepare("
                        UPDATE $table_name set $keys
                        WHERE id=:id"
                    );
                $row[":id"] = $id;
                return $statement->execute($row); 
            }
            catch(PDOException $e){
                dd("$e");
            }
        }
    }