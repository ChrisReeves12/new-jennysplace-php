<?php
/**
* The Theme class definition.
*
* Themes represent the look and feel of the site. They are not database driven but they reside in the themese folder in the public directory
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Page;

/**
 * Class Theme
 * @package Library\Model\Page
 */
class Theme
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $description;

    /**
     * Returns an array of eligible themes found in the themes folder
     * @return Theme[]
     */
    static function getThemes()
    {
        // Find themes in the appropriate directory
        $themes_folder = getcwd() . '/public/themes';
        $folders = scandir($themes_folder);

        // Go through the folders and check for the config file
        $themes = [];
        foreach ($folders as $folder)
        {
            if ($folder != '.' && $folder != '..' && is_dir($themes_folder . '/' . $folder))
            {
                if (file_exists($themes_folder . '/' . $folder . '/config.json'))
                {
                    // Attempt to read the contents of the file
                    $failed = false;
                    $config_file = $themes_folder . '/' . $folder . '/config.json';

                    $theme_info = json_decode(file_get_contents($config_file));
                    if (json_last_error())
                    {
                        $failed = true;
                    }

                    if (!$failed)
                    {
                        // Set the required parameters
                        $theme = new Theme();
                        if (!empty($theme_info->theme_name) && !empty($theme_info->description))
                        {
                            $theme->setName($theme_info->theme_name);
                            $theme->setDescription($theme_info->description);
                            $theme->setPath($themes_folder . '/' . $folder);
                            $themes[] = $theme;
                        }

                    }
                }
            }
        }

        // Return themes
        return $themes;
    }

    /**
     * Returns a theme by folder name if the theme is eligible or false on failure.
     * @param string $folder_name
     * @return Theme | bool
     */
    static function findByFolder($folder_name)
    {
        $theme_folder = getcwd() . '/public/themes/' . $folder_name;

        // Check if folder exists
        if (file_exists($theme_folder) && is_dir($theme_folder))
        {
            // Check for config file
            if (file_exists($theme_folder . '/config.json'))
            {
                // Attempt to read the contents
                $theme_info = json_decode(file_get_contents($theme_folder . '/config.json'));
                if (!empty($theme_info->theme_name) && !empty($theme_info->description))
                {
                    $theme = new Theme();
                    $theme->setName($theme_info->theme_name);
                    $theme->setDescription($theme_info->description);
                    $theme->setPath($theme_folder);
                    return $theme;
                }
                else
                {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
}