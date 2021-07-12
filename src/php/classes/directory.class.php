<?
	namespace directory;

	class directory {
		private $base_dir;
		private $files;       // array of file objects

		public function __construct($base_dir, $files_to_exclude = array()) {
			$this->base_dir = $base_dir;
			$this->files = self::get_directory_listing($this->base_dir, $files_to_exclude);

			return $this;
		}

		public function get_file($file_path) {
			if ($this->file_exists($file_path))
				return $this->files[$file_path];
			else
				return false;
		}

		public function get_files() : array {
			return $this->files;
		}

		public function get_dir() : string {
			return $this->base_dir;
		}

		public function file_exists($file_path) : bool {
			return key_exists($file_path, $this->files);
		}

		public static function get_directory_listing($dir_path, $files_to_exclude = array(), $limit = 0) : array {
			$filter = function ($iter_file, $key, $iterator) use ($files_to_exclude, $dir_path) {
				$path       = $iter_file->getPath().'/'.$iter_file->getFilename();
				$pathname   = str_replace($dir_path, '', $path);

				return (!in_array($pathname, $files_to_exclude));
			};

			$rdi    = new \RecursiveDirectoryIterator($dir_path, \RecursiveDirectoryIterator::SKIP_DOTS);
			$rcfi   = new \RecursiveCallbackFilterIterator($rdi, $filter);
			$rii    = new \RecursiveIteratorIterator($rcfi, \RecursiveIteratorIterator::SELF_FIRST);

			$files = array();

			foreach ($rii as $iter_file) {

				$path       = $iter_file->getPath().'/'.$iter_file->getFilename();
				$pathname   = str_replace($dir_path, '', $path);

				$file = new file();
				$file
					->set_name($iter_file->getFilename())
					->set_path($pathname)
					->set_full_path($path)
					->set_date($iter_file->getMTime())
					->set_level($rii->getDepth())
					->set_parent_name(dirname($pathname));

				if ($rii->hasChildren()) {
					$num_children = 0;

					$children_files = new \RecursiveIteratorIterator($rii->getChildren(), \RecursiveIteratorIterator::CHILD_FIRST);

					foreach ($children_files as $child_file)
						$num_children++;

					$file->set_num_children($num_children);
				}

				if ($iter_file->isDir())
					$file->set_dir(true);

				if ($iter_file->isFile())
					$file->set_file(true);

				if ($pathname != '')
					$files[$file->get_path()] = $file;
			}

			return $files;
		}

		public static function get_num_children_files($dir_path) : int {
			if (is_dir($dir_path)) {
				$rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir_path, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);

				return iterator_count($rii);
			} else
				return 0;
		}

		public static function get_directory_changes(directory $directory, directory $directory_other, $all_files = false, $limit = LIMIT_FILES, $start = 0) : \changes {
			if (!$all_files)
				$all_files = self::combine_directories($directory, $directory_other);

			$changes = new \changes();

			$i = 0;

			foreach ($all_files as $file_path) {

				$file       = $directory->get_file($file_path);
				$file_other = $directory_other->get_file($file_path);
				$dont_add   = false;

				$change = new \change();

				if ($file) {
					$change->set_object($file);

					if ($file_other) {

						$dont_add = $file->is_dir() || $file_other->is_dir();

						$change->set_object_other($file_other);

						if ($file->get_date() > $file_other->get_date())
							$change->add_reason('newer');
						elseif ($file->get_date() < $file_other->get_date())
							$change->add_reason('older');
						else
							$dont_add = true;
					} else
						$change->add_reason('new');
				} else {
					$change
						->set_object($file_other)
						->add_reason('dne');
				}

				if (!$dont_add) {
					$change->set_data('i', $i); // mark for removal. doesn't do anything productive.

					if ($i < $start) {
						$start--;

						continue;
					}

					$changes->add($change);

					$i++;
				}

				if ($i >= $limit)
					break;
			}

			return $changes;
		}

		public static function combine_directories(directory $dir_1, directory $dir_2) {
			$func_combine = function(directory $dir, $dir_list = array()) {
//				$files = self::get_flat_directory($dir);
				$files = $dir->get_files();

				foreach ($files as $file) {

					if (!in_array($file->get_path(), $dir_list))
						$dir_list[] = $file->get_path();
				}

				return $dir_list;
			};

			$dirs = $func_combine($dir_1);
			$dirs = $func_combine($dir_2, $dirs);

			return $dirs;
		}
	}

	class file {
		private
			$name,
			$path,
			$full_path,
			$date,
			$parent_name;

		private
			$file = false,
			$dir = false;

		private
			$level = 0,
			$num_children = 0;

		public function set_name($name) : file {
			$this->name = $name;

			return $this;
		}

		public function set_path($path) : file {
			$this->path = $path;

			return $this;
		}

		public function set_full_path($full_path) : file {
			$this->full_path = $full_path;

			return $this;
		}

		public function set_date($date) : file {
			$this->date = $date;

			return $this;
		}

		public function set_file(bool $file) : file {
			$this->file = $file;

			return $this;
		}

		public function set_dir(bool $dir) : file {
			$this->dir = $dir;

			return $this;
		}

		public function set_parent_name($parent_name) : file {
			$this->parent_name = $parent_name;

			return $this;
		}

		public function set_level(int $level) : file {
			$this->level = $level;

			return $this;
		}

		public function set_num_children(int $num_children) : file {
			$this->num_children = $num_children;

			return $this;
		}

		public function get_name() : string {
			return $this->name;
		}

		public function get_path() : string {
			return $this->path;
		}

		public function get_full_path() : string {
			return $this->full_path;
		}

		public function get_date() {
			return $this->date;
		}

		public function get_parent_name() {
			return $this->parent_name;
		}

		public function is_file() : bool {
			return $this->file;
		}

		public function is_dir() : bool {
			return $this->dir;
		}

		public function has_children() : bool {
			return (!empty($this->children));
		}

		public function get_level() : int {
			return $this->level;
		}

		public function get_num_children() : int {
			return $this->num_children;
		}
	}