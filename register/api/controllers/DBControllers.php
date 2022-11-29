<?php
interface DBControllers {
    public function get($id);
    public function delete($id);
    public function create($post);
    public function update($post, $id);
}