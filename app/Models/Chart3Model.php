<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Chart3Model extends Model {
	// Table name is assumed to be pluralized by Laravel
	protected $table = 'chart3';
	protected $primaryKey = 'invoice_external_id';
	protected $keyType = 'string';
	public $timestamps = false;
	protected $fillable = ['invoice_external_id','invoice_status', 'total_invoices', 'total_payments', 'total_paid_amount', 'total_invoiced_amount', 'outstanding_amount'];
	private $invoice_status;
	private $invoice_external_id;
	private $total_invoices;
	private $total_payments;
	private $total_paid_amount;
	private $total_invoiced_amount;
	private $outstanding_amount;
	private $erreurs = [];

	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
		if (!empty($attributes)) {
			$this->invoice_status = $attributes['invoice_status'] ?? null;
			$this->invoice_external_id = $attributes['invoice_external_id'] ?? null;
			$this->total_invoices = $attributes['total_invoices'] ?? null;
			$this->total_payments = $attributes['total_payments'] ?? null;
			$this->total_paid_amount = $attributes['total_paid_amount'] ?? null;
			$this->total_invoiced_amount = $attributes['total_invoiced_amount'] ?? null;
			$this->outstanding_amount = $attributes['outstanding_amount'] ?? null;
		}
	}

	public function getInvoice_status() {
		return $this->invoice_status;
	}

	public function getInvoice_external_id() {
		return $this->invoice_external_id;
	}

	public function getTotal_invoices() {
		return $this->total_invoices;
	}

	public function getTotal_payments() {
		return $this->total_payments;
	}

	public function getTotal_paid_amount() {
		return $this->total_paid_amount;
	}

	public function getTotal_invoiced_amount() {
		return $this->total_invoiced_amount;
	}

	public function getOutstanding_amount() {
		return $this->outstanding_amount;
	}

	public function setInvoice_status($invoice_status) {
		$this->invoice_status = $invoice_status;
	}

	public function setInvoice_external_id($invoice_external_id) {
		$this->invoice_external_id = $invoice_external_id;
	}

	public function setTotal_invoices($total_invoices) {
		$this->total_invoices = $total_invoices;
	}

	public function setTotal_payments($total_payments) {
		$this->total_payments = $total_payments;
	}

	public function setTotal_paid_amount($total_paid_amount) {
		$this->total_paid_amount = $total_paid_amount;
	}

	public function setTotal_invoiced_amount($total_invoiced_amount) {
		$this->total_invoiced_amount = $total_invoiced_amount;
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
	public static function getAllChart3() {
		try {
			return self::orderBy('outstanding_amount', 'DESC')->get();
		} catch (\Exception $e) {
			throw new \RuntimeException('Erreur lors de la récupération de tous les Chart3: ' . $e->getMessage());
		}
	}

	public static function getChart3ById($id) {
		try {
			$result = self::findOrFail($id);
			if (!$result) {
				throw new \RuntimeException('Aucun Chart3 trouvé avec l\'ID : ' . $id);
			}
			return $result;
		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			throw new \RuntimeException('Erreur lors de la recherche du Chart3 : ' . $e->getMessage());
		} catch (\Exception $e) {
			throw new \RuntimeException('Erreur inattendue lors de la recherche : ' . $e->getMessage());
		}
	}
}
