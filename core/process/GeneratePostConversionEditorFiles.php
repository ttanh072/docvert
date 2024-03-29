<?php

/**

NOTE: should be inserted at a point in the pipeline where the $currentXml is DocBook

Purpose: make some file that live editor needs.

**/

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'webpage.php');

class GeneratePostConversionEditorFiles extends PipelineProcess 
	{
	
	public function process($currentXml)
		{

		if(!file_exists($this->generatePath('docvert--all-docbook.xml')))
			{
			$editableDocbook = xsltTransform($currentXml, $this->docvertTransformDirectory.'docbook-to-docbook-with-placeholders.xsl');
			$this->saveFile('docvert--all-docbook.xml', $editableDocbook);
			}

		
		$chosenTheme = getGlobalConfigItem('theme');
		if($chosenTheme == null)
			{
			$chosenTheme = 'docvert';
			}

		$editorTemplatePath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$chosenTheme.DIRECTORY_SEPARATOR.'fckeditor-template.html';
		$editorTemplate = file_get_contents($editorTemplatePath);
		$editorTemplate = preg_replace_callback('/\&(.*?)\;/s', 'replaceLanguagePlaceholder', $editorTemplate);

		include_once(dirname(__FILE__).'/DocBookToXHTML.php');
		$toHtmlObject = new DocBookToXHTML($this->elementAttributes, $this->pipelineDirectory, $this->contentDirectory, $this->docvertTransformDirectory, $this->loopDepth, $this->depthArray, $this->previewDirectory, $this->pipelineSettings);
		$html = $toHtmlObject->process($currentXml);

		$documentTitlePattern = "/<title[^>]*?>(.*?)<\\/title>/sm";
		preg_match($documentTitlePattern, $html, $matches);
		$documentTitle = trim($matches[1]);
		$documentTitle = preg_replace("/<.*?>/", '', $documentTitle);

		//die($documentTitle.'<hr />'.$html);

		$html = substringAfter($html, '<body');
		$html = substringAfter($html, '>');
		$html = substringBefore($html, '</body');

		$paragraphTitlePattern = "/<p.*?documentTitle[^>]*?>(.*?)<\\/p>/sm";
		$html = preg_replace($paragraphTitlePattern, '', $html);

		$imagePathPrefix = '../../../../writable/'.basename($this->previewDirectory).'/'.basename($this->contentDirectory).'/';
		$html = str_replace('src="', 'src="'.$imagePathPrefix, $html);
		//displayXmlString($html);
		$html = str_replace('&', '&amp;', $html);
		$html = str_replace('<', '&lt;', $html);
		$html = str_replace('>', '&gt;', $html);
		$html = str_replace('"', '&quot;', $html);
		$html = trim($html);

		$autoPipeline = $this->pipelineSettings['autopipeline'];
		$autoPipeline = str_replace('.xml', '', $autoPipeline);
		$autoPipeline = str_replace('.default', '', $autoPipeline);

		$editorTemplate = str_replace('{{value}}', $html, $editorTemplate);
		$editorTemplate = str_replace('{{config}}', '', $editorTemplate);
		$editorTemplate = str_replace('{{documentTitle}}', $documentTitle, $editorTemplate);
		$editorTemplate = str_replace('{{documentPath}}', basename($this->previewDirectory).'/'.basename($this->contentDirectory), $editorTemplate);
		$editorTemplate = str_replace('{{pathToRemove}}', $imagePathPrefix, $editorTemplate);
		$editorTemplate = str_replace('{{pipeline}}', $this->pipelineSettings['pipeline'], $editorTemplate);
		$editorTemplate = str_replace('{{autopipeline}}', $autoPipeline, $editorTemplate);

		$this->saveFile('docvert--all-html.html', $editorTemplate);

		return $currentXml;
		}

	function generatePath($path)
		{
		$destinationFilename = processDepthTemplate($path, $this->depthArray);
		$destinationPath = $this->contentDirectory.DIRECTORY_SEPARATOR.$destinationFilename;
		return $destinationPath;
		}
	
	function saveFile($path, $data)
		{
		$destinationPath = $this->generatePath($path);
		file_put_contents($destinationPath, $data);
		}

	}
			
?>
