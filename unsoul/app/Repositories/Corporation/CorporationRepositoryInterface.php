<?php
namespace App\Repositories\Corporation;

interface CorporationRepositoryInterface {
	public function findById($id);
	public function search(Array $conditions = []);
	public function findByIdWithRelated($id);
	public function save(Array $data);
}