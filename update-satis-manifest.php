#!/usr/bin/env php
<?php

namespace Roots\WordPressSelfUpdate;

/**
 * @param string $version
 * @param string $zipURL
 * @return array
 */
function makeComposerPackagePair($version, $zipURL)
{
  return [
      [
      'type' => 'package',
      'package' => [
        'name' => 'roots/wordpress',
        'version' => $version,
        'require' => [
          'php' => '>=5.3.2',
          'johnpbloch/wordpress-core-installer' => '^1.0'
        ],
        'type' => 'wordpress-core',
        'dist' => [
          'url' => $zipURL,
          'type' => 'zip'
        ]
      ]
    ]
  ];
}

/**
 * @param array $packages
 * @return array
 */
function makeSatisManifest($packages)
{
  return [
    'name' => 'Roots Packages',
    'homepage' => 'https://packages.roots.io',
    'repositories' => $packages,
    'require-all' => true
  ];
}

function updateSatisManifest($satisFilePath, $wpReleasesManifest)
{
  $wpReleases = json_decode(file_get_contents($wpReleasesManifest));
  $packages = [];
  foreach($wpReleases as $release) {
      array_push($packages, ...makeComposerPackagePair($release->name, $release->zipball_url));
  }
  
  $json_options = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
  return file_put_contents(
    $satisFilePath,
    json_encode(makeSatisManifest($packages), $json_options)
  );
}

// if run on cli
if ($argv && $argv[0] && realpath($argv[0]) === __FILE__) {
  $usage = "usage: update-satis-manifest.php {{satis.json path}} {{wp releases json path}}\n";
  
  $args = array_slice($argv, 1);
  if (count($args) === 1 && in_array($args[0], ['-h', '--help'])) {
    echo $usage;
    exit(0);
  }
  
  if (count($argv) < 2) {
    echo $usage;
    exit(1);
  }
  
  $result = updateSatisManifest($args[0], $args[1]);
  
  fwrite(STDERR, ($result ? 'updated successfully' : 'update failed') . "\n");
  exit($result ? 0 : 1);
  
}