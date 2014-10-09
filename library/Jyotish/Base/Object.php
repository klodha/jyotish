<?php
/**
 * @link      http://github.com/kunjara/jyotish for the canonical source repository
 * @license   GNU General Public License version 2 or later
 */

namespace Jyotish\Base;

use Jyotish\Graha\Graha;
use Jyotish\Ganita\Math;

/**
 * Base class for Jyotish objects.
 *
 * @author Kunjara Lila das <vladya108@gmail.com>
 */
class Object {
	/**
	 * Type of object.
	 * 
	 * @var string
	 */
	protected $objectType = null;

	/**
	 * Environment - position of the planets in the format of the ganita output data.
	 * 
	 * @var array
	 */
	protected $ganitaData = null;
	
	/**
	 * Set environment.
	 * 
	 * @param array $ganitaData
	 */
	public function setEnvironment(array $ganitaData)
	{
		$this->ganitaData  = $ganitaData;
		
		if($this->objectType == 'rashi')
			$this->objectRashi = $this->objectKey;
		else
			$this->objectRashi = $this->ganitaData[$this->objectType][$this->objectKey]['rashi'];
	}
	
	/**
	 * Check the environment.
	 * 
	 * @throws Exception\UnderflowException
	 */
	protected function checkEnvironment()
	{
		if(is_null($this->ganitaData))
			throw new Exception\UnderflowException("Environment for object '{$this->objectType} {$this->objectKey}' must be setted.");
	}
	
	/**
	 * Get aspect by grahas.
	 * 
	 * @param null|array $options (Optional) Options to set
	 * @return array
	 */
	public function isAspectedByGraha($options = null)
	{
		$this->checkEnvironment();
		
		foreach (Graha::$graha as $key => $name){
			if($key == $this->objectKey) continue;
			
			$Graha = Graha::getInstance($key, $options);
			$grahaDrishti = $Graha->getGrahaDrishti();
			
			$distanse = Math::distanceInCycle(
				$this->ganitaData['graha'][$key]['rashi'], 
				$this->objectRashi
			);
			$isAspected[$key] = $grahaDrishti[$distanse];
		}
		return $isAspected;
	}
	
	/**
	 * Get connection with other grahas.
	 * 
	 * @return array
	 */
	public function isConnected()
	{
		$this->checkEnvironment();
		
		$isConnected = array();
		
		foreach (Graha::$graha as $key => $name){
			if($key == $this->objectKey) continue;
			
			if($this->ganitaData['graha'][$key]['rashi'] == $this->objectRashi){
				$isConnected[$key] = $name;
			}
		}
		return $isConnected;
	}
	
	/**
	 * Returns an array of hemming grahas.
	 * 
	 * @return array
	 */
	public function isHemmed()
	{
		$this->checkEnvironment();
		
		$isHemmed = array();
		$p = 'prev';
		$n = 'next';
		
		$$p = Math::numberPrev($this->objectRashi);
		$$n = Math::numberNext($this->objectRashi);
		
		foreach (Graha::$graha as $key => $name){
			if($key == $this->objectKey) continue;
			
			if($this->ganitaData['graha'][$key]['rashi'] == ${$n})
				$isHemmed[$key] = $n;
			elseif($this->ganitaData['graha'][$key]['rashi'] == ${$p})
				$isHemmed[$key] = $p;
		}
		
		if(!(array_search($p, $isHemmed) and array_search($n, $isHemmed)))
			$isHemmed = array();
		
		return $isHemmed;
	}
}
