# Staging / Production Directory Comparison Tool

Tool for comparing directories and pushing certain (or all) files from staging to production and vise-versa.

## Installation

Installation is pretty simple. It requires very little configuration. 

1. Download the latest release
2. Edit the configuration file. See the <a href="https://github.com/edonnel/staging-directory-comparison/wiki#configuration">wiki</a> 
   for configuration details.
3. Require `module.php`

Please read the <a href="https://github.com/edonnel/staging-directory-comparison/wiki">wiki</a> for important 
implementation details.

## Dependencies

This repo uses a couple submodules I made. They need to be downloaded and installed in order for this to function properly. The submodule directory is `/lib`.

### result.class.php

Place in `/lib/result/`.<br />
Repo: https://github.com/edonnel/result.class.php

### changes.class.php

Place in `/lib/changes/`.<br />
Repo: https://github.com/edonnel/changes.class.php

## License

This code is released under the GNU Lesser General Public License (LGPL). For more information, visit http://www.gnu.org/copyleft/lesser.html
