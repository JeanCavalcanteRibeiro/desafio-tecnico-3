<?php
// # Ordenação Externa de Arquivos

// Você deve escrever um script PHP que seja capaz de ordenar grandes conjuntos de dados contidos em arquivos externos. O desafio consiste em criar um algoritmo de ordenação externa eficiente que possa lidar com arquivos de entrada de tamanho arbitrário, garantindo que o processo de ordenação seja eficiente em termos de uso de memória e tempo de execução.

// ## Requisitos:

// - O script deve ser capaz de lidar com arquivos de entrada de tamanho arbitrário, potencialmente maiores do que a memória disponível no servidor.
// - O script deve conter um algoritmo de ordenação próprio, não utilizando assim funções de ordenação padrões.
// - O algoritmo de ordenação deve ser eficiente em termos de tempo de execução e uso de recursos.
// - O script deve ser capaz de ordenar os dados em ordem ascendente ou descendente, conforme especificado.
// - O script deve ser bem documentado e fácil de entender para outros desenvolvedores.
// - O script deve ser feito em PHP puro, sem uso de frameworks.

// ## Dicas:

// - Crie um arquivo de entrada contendo um grande conjunto de números não ordenados.
// - Execute o script PHP fornecido para ordenar o arquivo de entrada.
// - Verifique se o arquivo de saída está corretamente ordenado.

# My comments will be in English, as will the code itself.


/**
 * Class ExternalSort
 * 
 * Represents a class for performing external sorting on a large file. Will not always respect 100% of the memory limits, but the sorting itself should be done in chunks.
 * 
 * @package ThisDoesNotHaveAPackageThanksForAskingPHPDoc
 */
class ExternalSort
{
	/**
	 * @var string The path to the temporary folder used for storing temporary files.
	 */
	private $temporary_folder;
	/**
	 * @var string The path to the input file.
	 */
	private $input_file;

	/**
	 * @var string The path to the output file.
	 */
	private $output_file;

	/**
	 * @var int The size of each chunk to be sorted in memory, in bytes.
	 */
	private $chunk_size;

	/**
	 * @var int The memory limit (in bytes) for sorting the chunks in memory.
	 */
	private $memory_limit;

	/**
	 * @var string The sort order for the elements in the file. Can be 'asc' or 'desc'.
	 */
	private $sort_order;
	/*
	 * @var int The total number of elements in the input file.
	 */
	private $total_elements;

	/**
	 * ExternalSort constructor.
	 *
	 * @param string $temporary_folder The path to the temporary folder used for storing temporary files.
	 * @param string $input_file The path to the input file.
	 * @param string $output_file The path to the output file.
	 * @param int $chunk_size The size of each chunk to be sorted in memory, in bytes.
	 * @param int $memory_limit The memory limit (in bytes) for sorting the chunks in memory.
	 * @param string $sort_order The sort order for the elements in the file.
	 */
	public function __construct($temporary_folder, $input_file, $output_file, $chunk_size, $memory_limit, $sort_order)
	{
		$this->temporary_folder = $temporary_folder;
		$this->input_file = $input_file;
		$this->output_file = $output_file;
		$this->chunk_size = $chunk_size;
		$this->memory_limit = $memory_limit;
		$this->sort_order = $sort_order;
	}

	/**
	 * Destructor to delete temporary files.
	 */
	public function __destruct()
	{
		$this->deleteTemporaryFiles();
	}

	public function external_sort()
	{
		$this->createTemporaryFolder();
		// $this->sortChunks();
		// $this->mergeChunks();
	}

	// This is a barebones implementation that probably is around on some textbook somewhere. This is not going to come closer to actual optimized quicksort implementations.
	public function barebones_quicksort($array)
	{
		if (count($array) < 2) {
			return $array;
		}

		$left = $right = [];

		reset($array);
		$pivot_key = key($array);
		$pivot = array_shift($array);
		
		foreach ($array as $key => $value) {
			if ($value < $pivot) {
				$left[$key] = $value;
			} else {
				$right[$key] = $value;
			}
		}

		return array_merge($this->barebones_quicksort($left), [$pivot_key => $pivot], $this->barebones_quicksort($right));
	}





	/**
	 * Creates a temporary folder for storing chunked files.
	 */
	private function createTemporaryFolder()
	{
		if (!file_exists($this->temporary_folder)) {
			mkdir($this->temporary_folder, 0777, true);
		}
	}

	/**
	 * Deletes the temporary files created during the sorting process.
	 */
	private function deleteTemporaryFiles()
	{
		$files = glob($this->temporary_folder . '/*');
		foreach ($files as $file) {
			unlink($file);
		}
	}
}

