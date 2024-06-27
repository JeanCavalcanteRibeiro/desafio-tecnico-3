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
		$chunk_count = $this->sortChunks();
		$this->mergeChunks($chunk_count);
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
	 * Sorts the chunks of the input file in memory and writes the sorted chunks to temporary files on disk.
	 * 
	 * IMPORTANT: This ASSUMES that we have enough disk space to store ALL temporary files at once. A complete implementation would involve
	 * 			  knowing the available disk space and handling the case where we run out of disk space, which I am not doing here.
	 *
	 * @return int The number of chunks created.
	 */
	private function sortChunks()
	{
		$input_handle = fopen($this->input_file, 'r');

		// Initialize the chunk counter, used for naming temporary files.
		$chunk_counter = 0;

		while (!feof($input_handle)) {
			$chunk = fread($input_handle, $this->chunk_size);

			// Convert the chunk to an array of elements. Note that due to PHP shenaningans, its possible for this to go over our set memory limit in some cases.
			$elements = explode("\n", $chunk);
			$this->total_elements += count($elements);

			// Sort the elements in memory.
			$elements = $this->barebones_quicksort($elements);

			// Yep. Not doing it the hard way this time.
			if ($this->sort_order === 'desc') {
				$elements = array_reverse($elements);
			}

			$temp_file = $this->temporary_folder . '/chunk_' . $chunk_counter . '.txt';
			file_put_contents($temp_file, implode("\n", $elements));

			$chunk_counter++;
		}

		// Close the input file handle.
		fclose($input_handle);

		// Return the number of chunks created.

		return $chunk_counter;
	}


	/**
	 * Merges the sorted chunks into a single output file.
	 * 
	 * TODO: proper constraint memory usage when reading files. This implementation will read all files at once, which is not ideal.
	 */
	private function mergeChunks($chunk_count)
	{

		// Open the temporary files for reading. This COMPLETELY ignores $this->memory_limit, as we have all files open at once.
		// A complete implementation would involve reading a limited number of elements from each file at a time, and that would add I/O complexity to the code.

		// Open the temporary files for reading.
		$chunk_handles = [];
		for ($i = 0; $i < $chunk_count; $i++) {
			$chunk_handles[$i] = fopen($this->temporary_folder . '/chunk_' . $i . '.txt', 'r');
		}

		// Open the output file for writing.
		$output_handle = fopen($this->output_file, 'w');

		// Initialize an array to store the current element of each chunk.
		$current_elements = array_fill(0, $chunk_count, false);

		// Read the first element of each chunk. Will also go over $this->memory_limit if there are too many chunks.
		for ($i = 0; $i < $chunk_count; $i++) {
			$current_elements[$i] = fgets($chunk_handles[$i]);
		}

		$total_sorted = 0;

		// Merge the chunks by selecting the smallest or largest element at each step.
		while (true) {
			// Find the index of the smallest or largest element.
			$index = ($this->sort_order === 'asc') ? $this->findSmallestElementIndex($current_elements) : $this->findLargestElementIndex($current_elements);

			// If all elements are false, we have reached the end of all chunks.
			if ($index === null) {
				break;
			}

			// Write the element to the output file.
			fwrite($output_handle, $current_elements[$index]);

			// Read the next element from the chunk that had the smallest or largest element.
			$current_elements[$index] = fgets($chunk_handles[$index]);

			// If the end of the chunk is reached, set the element to false.
			if ($current_elements[$index] === false) {
				$current_elements[$index] = false;
				$index = null;
			}
			
			$total_sorted++;
			if ($total_sorted % 10000 === 0) {
				printf("Sorted %d elements out of %d\n", $total_sorted, $this->total_elements);
				# Current memory usage, will go down with larger chunk sizes since there are less files open at once. Yep, not a very good implementation.
				// printf("Memory usage: %d\n", memory_get_usage());
			} else if ($total_sorted === $this->total_elements) {
				printf("Sorted %d elements out of %d\n", $total_sorted, $this->total_elements);
			}
		}

		// Close the temporary files.
		foreach ($chunk_handles as $handle) {
			fclose($handle);
		}

		// Close the output file.
		fclose($output_handle);
	}

	/**
	 * Finds the index of the smallest element in an array of elements.
	 *
	 * @param array $elements The array of elements.
	 *
	 * @return int The index of the smallest element.
	 */
	private function findSmallestElementIndex($elements)
	{
		$smallest_index = null;
		$smallest_value = null;

		foreach ($elements as $index => $value) {
			if ($value !== false && ($smallest_value === null || $value < $smallest_value)) {
				$smallest_index = $index;
				$smallest_value = $value;
			}
		}

		return $smallest_index;
	}

	/**
	 * Finds the index of the largest element in an array of elements. Yes, this is just a copy-paste of the previous function, but with the comparison inverted. Another to the list of things that could be improved.
	 *
	 * @param array $elements The array of elements.
	 *
	 * @return int The index of the largest element.
	 */
	private function findLargestElementIndex($elements)
	{
		$largest_index = null;
		$largest_value = null;

		foreach ($elements as $index => $value) {
			if ($value !== false && ($largest_value === null || $value > $largest_value)) {
				$largest_index = $index;
				$largest_value = $value;
			}
		}

		return $largest_index;
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

$current_folder = __DIR__;
$start_time = microtime(true);

$sorter = new ExternalSort($current_folder . '/tmp', 'input.txt', 'output.txt', 8192, 8192, 'desc');

$sorter->external_sort();

$end_time = microtime(true);
$elapsed_time = ($end_time - $start_time);
echo "Elapsed time: " . $elapsed_time . " seconds";
