<?php

/**
 * PHP prototype export
 * @author David Grudl
 * @license BSD
 */
class AdminerDumpPhpPrototype
{
	public $output = [];
	public $shutdown_callback = false;
	public $typePatterns = [
		'^_' => 'string', // PostgreSQL arrays
		'(SMALL|SHORT|MEDIUM|BIG|LONG)(INT)?|INT(EGER|\d+| IDENTITY)?|(SMALL|BIG|)SERIAL\d*|COUNTER|YEAR|BYTE|LONGLONG|UNSIGNED BIG INT' => 'int',
		'(NEW)?DEC(IMAL)?(\(.*)?|NUMERIC|REAL|DOUBLE( PRECISION)?|FLOAT\d*|(SMALL)?MONEY|CURRENCY|NUMBER' => 'float',
		'BOOL(EAN)?|TINYINT' => 'bool',
		'TIME' => 'time',
		'DATE' => 'date',
		'(SMALL)?DATETIME(OFFSET)?\d*|TIME(STAMP.*)?' => 'datetime',
		'BYTEA|(TINY|MEDIUM|LONG|)BLOB|(LONG )?(VAR)?BINARY|IMAGE' => 'binary',
	];
	public $phpTypes = [
		'date' => 'DateTimeImmutable',
		'datetime' => 'DateTimeImmutable',
		'binary' => 'string',
	];
	private $formats = [
		'code-insert' => 'Nette Database',
		'code-form' => 'Nette Form',
		'code-class' => 'Data Class',
	];


	public function dumpFormat()
	{
		return $this->formats;
	}


	public function dumpHeaders()
	{
		if (isset($this->formats[$_POST['format']])) {
			echo '<script' . nonce() . ' type="text/javascript" src="static/prism.js"></script>';
			echo '<link rel="stylesheet" href="static/prism.css">';
			return $_POST['format'];
		}
	}


	public function dumpTable($table)
	{
		if ($_POST['format'] == 'code-insert') {
			ob_start();
			$this->exportAsInsertQuery($table);
			$this->printCode(ob_get_clean());

		} elseif ($_POST['format'] == 'code-form') {
			ob_start();
			$this->exportAsForm($table);
			$this->printCode(ob_get_clean());

		} elseif ($_POST['format'] == 'code-class') {
			ob_start();
			$this->exportAsDataClass($table, false);
			$this->printCode(ob_get_clean());
			if (PHP_VERSION_ID >= 80000) {
				ob_start();
				$this->exportAsDataClass($table, true);
				$this->printCode(ob_get_clean());
			}

		} else {
			return;
		}

		return true;
	}


	private function printCode($code)
	{
		echo '<pre style="user-select:all"><code class="language-php">',
			htmlspecialchars($code),
			'</cody></pre>';
	}


	private function exportAsInsertQuery($table)
	{
		echo "\$db->query('INSERT INTO " . table($table) . "', [\n";
		foreach (fields($table) as $field => $foo) {
			echo "\t'$field' => \$$field,\n";
		}
		echo "]);\n\n";
	}


	private function exportAsForm($table)
	{
		foreach (fields($table) as $field => $info) {
			if (!empty($info['auto_increment'])) {
				continue;
			}

			$label = ucfirst(str_replace('_', ' ', $field));
			$args = var_export($field, true) . ', ' . var_export($label . ':', true);
			$type = $this->detectType($info['type']);

			if ($type === 'bool') {
				echo '$form->addCheckbox(', var_export($field, true), ', ', var_export($label, true), ')';
				$info['null'] = true;
			} elseif ($type === 'int' && strpos($field, '_id')) {
				echo "\$form->addSelect($args)";
			} elseif ($type === 'int') {
				echo "\$form->addInteger($args)";
			} elseif ($type === 'datetime') {
				echo "\$form->addText($args)\n\t->setHtmlType('datetime-local')";
			} elseif ($type === 'date') {
				echo "\$form->addText($args)\n\t->setHtmlType('date')";
			} elseif ($type === 'time') {
				echo "\$form->addText($args)\n\t->setHtmlType('time')";
			} elseif ($type === 'float') {
				echo "\$form->addText($args)\n\t->addRule(\$form::FLOAT)";
			} elseif ($type === 'string' && strpos($info['type'], 'text') === false) {
				if (strpos($field, 'email') === false) {
					echo "\$form->addText($args)";
				} else {
					echo "\$form->addEmail($args)";
				}
			} elseif ($type === 'string') {
				echo "\$form->addTextArea($args)";
			} else {
				echo "\$form->addText($args)";
			}

			if (!$info['null']) {
				echo "\n\t->setRequired()";
			}

			$length = (int) $info['length'];
			if ($length && $type === 'string') {
				echo "\n\t->addRule(\$form::MAX_LENGTH, null, $length)";
			}

			echo ";\n";
		}
		echo "\$form->addSubmit('send');\n";
		echo "\$form->addProtection();\n";
		echo "\$form->onSuccess[] = [\$this, 'formSucceeded'];\n";
		echo "\n\n";
	}


	private function exportAsDataClass($table, $promo)
	{
		$class = ucwords(str_replace('_', ' ', $table));
		$class = preg_replace('~\W~', '', $class) . 'FormData';
		echo "class $class\n";
		echo "{\n";
		echo "\tuse Nette\\SmartObject;\n\n";
		if ($promo) {
			echo "\tpublic function __construct(\n";
		}
		foreach (fields($table) as $field => $info) {
			$type = $this->detectType($info['type']);
			$type = isset($this->phpTypes[$type]) ? $this->phpTypes[$type] : $type;
			if ($info['null']) {
				$type = '?' . $type;
			}
			if (PHP_VERSION_ID >= 70400) {
				echo $promo ? "\t" : '';
				echo "\tpublic $type \$$field";
			} else {
				echo "\n\t/** @var $type */\n";
				echo "\tpublic \$$field";
			}
			$default = $info['default'];
			if ($default !== null && $default !== 'CURRENT_TIMESTAMP') {
				@settype($default, $type); // may be invalid type
				echo ' = ' . var_export($default, true);
			}
			echo $promo ? ",\n" : ";\n";
		}
		if ($promo) {
			echo "\t) {\n\t}\n";
		}
		echo "}\n";
	}


	public function dumpData($table, $style, $query)
	{
		if (isset($this->formats[$_POST['format']])) {
			return true;
		}
	}


	public function detectType($type)
	{
		static $cache;
		if (!isset($cache[$type])) {
			$cache[$type] = 'string';
			foreach ($this->typePatterns as $s => $val) {
				if (preg_match("#^($s)$#i", $type)) {
					return $cache[$type] = $val;
				}
			}
		}
		return $cache[$type];
	}
}
