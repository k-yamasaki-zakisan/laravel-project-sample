<?php
namespace App\Repositories\Prefecture;

interface PrefectureRepositoryInterface {
	public function search(Array $conditions = []);
}