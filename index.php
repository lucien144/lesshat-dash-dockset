<?php
require_once __DIR__ . '/vendor/autoload.php';
use \Michelf\Markdown;
dibi::connect(array(
    'driver'   => 'sqlite3',
    'database'     => __DIR__ . '/build/LessHat.docset/Contents/Resources/docSet.dsidx',
));


/**
 * Get the docs, parse the MD and save it as HTML
 */
$lesshatDocsMd       = file_get_contents('https://raw.githubusercontent.com/madebysource/lesshat/master/README.md');
$css                 = file_get_contents('https://raw.githubusercontent.com/sindresorhus/github-markdown-css/gh-pages/github-markdown.css');
$docsStartPosition   = strpos($lesshatDocsMd, '## <a name="documentation"></a> Documentation:');
$thanksStartPosition = strpos($lesshatDocsMd, '## Big Thanks to:');

$lesshatDocsHtml = substr($lesshatDocsMd, $docsStartPosition);
$lesshatDocsHtml = substr($lesshatDocsHtml, 0, strpos($lesshatDocsHtml, '## Big Thanks to:'));
$lesshatDocsHtml = Markdown::defaultTransform($lesshatDocsHtml);

$lesshatIndexHtml = substr($lesshatDocsMd, 0, $docsStartPosition);
$lesshatIndexHtml .= substr($lesshatDocsMd, $thanksStartPosition);
$lesshatIndexHtml = str_replace('[![Build Status](https://travis-ci.org/madebysource/lesshat.png)](https://travis-ci.org/madebysource/lesshat)', '', $lesshatIndexHtml);
$lesshatIndexHtml = Markdown::defaultTransform($lesshatIndexHtml);

$tpl      = file_get_contents(__DIR__ . '/template.html');
$indexTpl = str_replace(array('{$CONTENT}', '{$CSS}'), array($lesshatIndexHtml, $css), $tpl);
$docsTpl  = str_replace(array('{$CONTENT}', '{$CSS}'), array($lesshatDocsHtml, $css), $tpl);

file_put_contents(__DIR__ . '/build/LessHat.docset/Contents/Resources/Documents/index.html', $indexTpl);
file_put_contents(__DIR__ . '/build/LessHat.docset/Contents/Resources/Documents/docs.html', $docsTpl);


/**
 * Creating the SQL index
 */
preg_match_all('/([0-9]*)\.\s\*\*\[([a-zA-Z0-9\-]*)\]\(\#([a-zA-Z0-9\-]*)\)\*\*\s(`[a-zA-Z0-9\-]*`)?/', $lesshatDocsMd, $matches);
dibi::query('DELETE FROM [searchIndex]');
foreach($matches[2] as $mixin) {
	dibi::query('INSERT OR IGNORE INTO searchIndex', array(
		'name' => $mixin,
		'type' => 'Mixin',
		'path' => 'docs.html#' . $mixin,
	));
}

echo 'Done!';