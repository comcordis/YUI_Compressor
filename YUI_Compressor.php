<?php

abstract class YUI_Compressor
{
	public static $javaExecutable = 'java';
	public static $phpExecutable = '/usr/bin/php';
	
	public static $yuiCompressorJAR = '';
	
	public static function initialize ()
	{
		if (XXX_OperatingSystem::$platformName == 'windows')
		{
			self::$javaExecutable = 'C:\\Windows\\System32\\java.exe';
		}
		else if (XXX_OperatingSystem::$platformName == 'linux')
		{
			self::$javaExecutable = 'java';
		}
		
		$yuiCompressorJARPrefix = XXX_Path_Local::composeOtherProjectDeploymentSourcePathPrefix('YUI_Compressor');
		
		self::$yuiCompressorJAR = XXX_Path_Local::extendPath($yuiCompressorJARPrefix, array('build', 'yuicompressor-2.4.7.jar'));
		
		/*
		It occurs to me the new pattern replacement syntax includes the colon as a separator, which is a reserved path character on windows, which is probably the root of the problem.
		
		Place output in file outfile. If not specified, the YUI Compressor will
      default to the standard output, which you can redirect to a file.
      Supports a filter syntax for expressing the output pattern when there are
      multiple input files.  ex:
          java -jar yuicompressor.jar -o '.css$:-min.css' *.css
      ... will minify all .css files and save them as -min.css
      
		*/
		
		$yuiCompressorJARParentPath = XXX_Path_Local::getParentPath(self::$yuiCompressorJAR);
		
		$changeWorkingDirectoryPrefix = '';
		
		if (XXX_OperatingSystem::$platformName == 'windows')
		{
			$changeWorkingDirectoryPrefix = 'cd ' . $yuiCompressorJARParentPath . ' && ';
		}
		else if (XXX_OperatingSystem::$platformName == 'linux')
		{
			$changeWorkingDirectoryPrefix = 'cd ' . $yuiCompressorJARParentPath . ' && ';
		}
		
		XXX_CommandLineHelpers::addCommandTemplate('executeCSSCompressor', $changeWorkingDirectoryPrefix . '%javaExecutable% -jar %yuiCompressorJAR% "%inputFile%" --line-break 0 -o "%outputFile%" --charset %characterSet%', 'details', 'replaceVariables');
		
		XXX_CommandLineHelpers::addCommandTemplate('executeJSCompressor', $changeWorkingDirectoryPrefix . '%javaExecutable% -jar %yuiCompressorJAR% "%inputFile%" --line-break 0 --preserve-semi --type js -o "%outputFile%" --charset %characterSet%', 'details', 'replaceVariables');
	}
	
	public static function compressCSSFile ($inputFile = '', $outputFile = '', $characterSet = 'utf-8')
	{
		$outputFile = XXX_Path_Local::makePathRelative(self::$yuiCompressorJAR, $outputFile);
		
		$result = XXX_CommandLineHelpers::executeCommandTemplate('executeCSSCompressor', array('javaExecutable' => self::$javaExecutable, 'yuiCompressorJAR' => self::$yuiCompressorJAR, 'inputFile' => $inputFile, 'outputFile' => $outputFile, 'characterSet' => $characterSet));
		
		if ($result['statusCode'] != '0')
		{
			trigger_error('Failed to compress css file: "' . $inputFile . '" to  "' . $outputFile . '".' . $result['rawOutput'] . ' ' . $result['rawErrorOutput'], E_USER_ERROR);
		}
		
		return $result;
	}
	
	public static function compressJSFile ($inputFile = '', $outputFile = '', $characterSet = 'utf-8')
	{	
		$outputFile = XXX_Path_Local::makePathRelative(self::$yuiCompressorJAR, $outputFile);
			
		$result = XXX_CommandLineHelpers::executeCommandTemplate('executeJSCompressor', array('javaExecutable' => self::$javaExecutable, 'yuiCompressorJAR' => self::$yuiCompressorJAR, 'inputFile' => $inputFile, 'outputFile' => $outputFile, 'characterSet' => $characterSet));
		
		if ($result['statusCode'] != '0')
		{
			trigger_error('Failed to compress js file: "' . $inputFile . '" to  "' . $outputFile . '".' . $result['rawOutput'] . ' ' . $result['rawErrorOutput'], E_USER_ERROR);
		}
		
		return $result;
	}
	
	public static function compressCSSString ($cssString = '')
	{
		$tempHash = XXX_String::getRandomHash();
				
		$tempInputFile = XXX_Path_Local::extendPath(XXX_Path_Local::$deploymentDataPathPrefix, array($tempHash . '.css'));
		$tempOutputFile = XXX_Path_Local::extendPath(XXX_Path_Local::$deploymentDataPathPrefix, array('compressed.' . $tempHash . '.css'));
		
		XXX_FileSystem_Local::writeFileContent($tempInputFile, $cssString);
		
		self::compressCSSFile($tempInputFile, $tempOutputFile);
		
		$result = XXX_FileSystem_Local::getFileContent($tempOutputFile);
		
		XXX_FileSystem_Local::deleteFile($tempInputFile);
		XXX_FileSystem_Local::deleteFile($tempOutputFile);
		
		return $result;
	}
	
	public static function compressJSString ($jsString = '')
	{
		$tempHash = XXX_String::getRandomHash();
				
		$tempInputFile = XXX_Path_Local::extendPath(XXX_Path_Local::$deploymentDataPathPrefix, array($tempHash . '.js'));
		$tempOutputFile = XXX_Path_Local::extendPath(XXX_Path_Local::$deploymentDataPathPrefix, array('compressed.' . $tempHash . '.js'));
		
		XXX_FileSystem_Local::writeFileContent($tempInputFile, $jsString);
		
		self::compressCSSFile($tempInputFile, $tempOutputFile);
		
		$result = XXX_FileSystem_Local::getFileContent($tempOutputFile);
		
		XXX_FileSystem_Local::deleteFile($tempInputFile);
		XXX_FileSystem_Local::deleteFile($tempOutputFile);
		
		return $result;
	}
}

YUI_Compressor::initialize();

?>