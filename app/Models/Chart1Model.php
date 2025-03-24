<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Chart1Model extends Model {
	// Table name is assumed to be pluralized by Laravel
	protected $table = 'chart1';
	protected $primaryKey = 'id_chart1';
	protected $keyType = 'string';
	public $timestamps = false;
	protected $fillable = ['client_id','external_id','company_name', 'total_invoices', 'total_invoiced_amount', 'total_paid_amount', 'outstanding_amount'];
	private $client_id;
	private $external_id;
	private $company_name;
	private $total_invoices;
	private $total_invoiced_amount;
	private $total_paid_amount;
	private $outstanding_amount;
	private $erreurs = [];

	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
		if (!empty($attributes)) {
			$this->client_id = $attributes['client_id'] ?? null;
			$this->external_id = $attributes['external_id'] ?? null;
			$this->company_name = $attributes['company_name'] ?? null;
			$this->total_invoices = $attributes['total_invoices'] ?? null;
			$this->total_invoiced_amount = $attributes['total_invoiced_amount'] ?? null;
			$this->total_paid_amount = $attributes['total_paid_amount'] ?? null;
			$this->outstanding_amount = $attributes['outstanding_amount'] ?? null;
		}
	}

	public function getClient_id() {
		return $this->client_id;
	}

	public function getExternal_id() {
		return $this->external_id;
	}

	public function getCompany_name() {
		return $this->company_name;
	}

	public function getTotal_invoices() {
		return $this->total_invoices;
	}

	public function getTotal_invoiced_amount() {
		return $this->total_invoiced_amount;
	}

	public function getTotal_paid_amount() {
		return $this->total_paid_amount;
	}

	public function getOutstanding_amount() {
		return $this->outstanding_amount;
	}

	public function setClient_id($client_id) {
		$this->client_id = $client_id;
	}

	public function setExternal_id($external_id) {
		$this->external_id = $external_id;
	}

	public function setCompany_name($company_name) {
		$this->company_name = $company_name;
	}

	public function setTotal_invoices($total_invoices) {
		$this->total_invoices = $total_invoices;
	}

	public function setTotal_invoiced_amount($total_invoiced_amount) {
		$this->total_invoiced_amount = $total_invoiced_amount;
	}

	public function setTotal_paid_amount($total_paid_amount) {
		$this->total_paid_amount = $total_paid_amount;
	}

	public function setOutstanding_amount($outstanding_amount) {
		$this->outstanding_amount = $outstanding_amount;
	}

	public function getErreurs() {
		return $this->erreurs;
	}

	public function setErreurs($field, $message) {
		$this->erreurs[] = [
		'field' => $field,
		'message' => $message,
		'details' => null,
		];
	}

	public function hasErrors() {
		return !empty($this->erreurs);
	}

	public static function getAllChart1() {
		try {
			return self::orderBy('client_id', 'DESC')->get();
		} catch (\Exception $e) {
			throw new \RuntimeException('Erreur lors de la récupération de tous les Chart1: ' . $e->getMessage());
		}
	}

	public static function getChart1ById($id) {
		try {
			$result = self::findOrFail($id);
			if (!$result) {
				throw new \RuntimeException('Aucun Chart1 trouvé avec l\'ID : ' . $id);
			}
			return $result;
		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			throw new \RuntimeException('Erreur lors de la recherche du Chart1 : ' . $e->getMessage());
		} catch (\Exception $e) {
			throw new \RuntimeException('Erreur inattendue lors de la recherche : ' . $e->getMessage());
		}
	}
}
