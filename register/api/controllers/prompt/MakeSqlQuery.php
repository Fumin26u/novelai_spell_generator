<?php 

trait MakeSqlQuery {
    protected function makeCreateSql($columnList, $table) {
        $sql = '';

        $columnList = array_column($this->columns, 'name');
        $sql .= "INSERT INTO {$table} (";
        $sql .= implode(', ', $columnList);
        $sql .= ') VALUES (:';
        $sql .= implode(', :', $columnList);
        $sql .= ')';

        return $sql;
    }

    protected function makeUpdateSql($columnList, $table) {
        $sql = '';

        $sql = "UPDATE {$table} SET \n";
        for ($i = 0; $i < count($columnList); $i++) {
            $columnName = $columnList[$i];
            if ($i === count($columnList) - 1) {
                $sql .= "{$columnName} = :{$columnName} \n";
            } else {
                $sql .= "{$columnName} = :{$columnName}, \n";
            }
        }
        $sql .= "WHERE {$table}_id = :{$table}_id";

        return $sql;
    }
}