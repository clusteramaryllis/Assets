<?php namespace Stolz\Assets\Laravel;

use Config;
use File;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class FlushPipelineCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'asset:flush';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Flush assets pipeline files.';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		// Determine the config namespace sepatator
		$glue = (file_exists(app_path('config/packages/stolz/assets/config.php'))) ? '::' : '.';

		// Get directory paths
		$pipeDir = Config::get("assets{$glue}pipeline_dir", 'min');
		$cssDir = public_path(Config::get("assets{$glue}css_dir", 'css') . DIRECTORY_SEPARATOR . $pipeDir);
		$jsDir = public_path(Config::get("assets{$glue}js_dir", 'js') . DIRECTORY_SEPARATOR . $pipeDir);

		// Ask for confirmation
		if( ! $this->option('force'))
		{
			$this->info(sprintf('All content of %s and %s will be deleted.', $cssDir, $jsDir));
			if( ! $this->confirm('Do you wish to continue? [yes|no]'))
				return;
		}

		// Purge assets
		$purgeCss = $this->purgeDir($cssDir);
		$purgeJs = $this->purgeDir($jsDir);

		if( ! $purgeCss or ! $purgeJs)
			return $this->error('Something went wrong');

		$this->info('Done!');
	}

	/**
	 * Purge directory.
	 *
	 * @param  string $directory
	 * @return boolean
	 */
	protected function purgeDir($directory)
	{
		if( ! File::isDirectory($directory))
			return true;

		if(File::isWritable($directory))
			return File::cleanDirectory($directory);

		$this->error($directory . ' is not writable');
		return false;
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['force', 'f', InputOption::VALUE_NONE, 'Do not prompt for confirmation'],
		];
	}
}
