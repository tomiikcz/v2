<?php
/**
 * Vivo CMS
 * Copyright (c) 2009 author(s) listed below.
 *
 * @version $Id: Entity.php 2150 2012-08-15 11:05:23Z mhajek $
 */
namespace Vivo\CMS\Model;

use Vivo;
use Vivo\Util;
use Vivo\Converter;
use Vivo\CMS;
use Vivo\CMS\Model\Entity\Lock;

class Entity {
	/**
	 * Universally Unique Identifier (UUID) of the entity instance.
	 * Value is set when entity is being instantiated. Never set or change value of this property.
	 * @see __construct
	 * @var string
	 */
	protected $uuid;
	/**
	 * Absolute path to entity stored in repository.
	 * @var string
	 */
	protected $path;
	/**
	 * Not used yet.
	 * @var Vivo\CMS\Model\Lock
	 */
// 	protected $lock;
	/**
	 * If TRUE, entity will be indexed by fulltext indexer.
	 * @see Vivo\CMS\Solr\Indexer
	 * @var boolean
	 */
	protected $searchable;
	/**
	 * Time of entity creation.
	 * @var DateTime
	 */
	protected $created;
	/**
	 * Username of entity creator.
	 * @var string
	 */
	protected $createdBy;
	/**
	 * Time of entity last modification.
	 * @var DateTime
	 */
	protected $modified;
	/**
	 * Username of user who made last last modification.
	 * @var string
	 */
	protected $modifiedBy;

	/**
	 * Constructor. Sets uuid property by value obtained from static method create_uuid().
	 * @param string $path Path to entity. If not set, it will be undefined and can be set later before persisting entity using saveEntity method of Repository.
	 * @see Vivo\CMS\DAO\Repository::saveEntity()
	 */
	public function __construct($path = null) {
		$this->path = $path;
	}

	/**
	 * Compare entities by path.
	 * @param \Vivo\CMS\Model\Entity $entity
	 * @return bool Returns true if the entity is under another entity (in the tree paths).
	 */
	public function under(\Vivo\CMS\Model\Entity $entity) {
		return (strpos($this->path.'/', $entity->getPath().'/') === 0);
	}

	/**
	 * @param string $uuid
	 */
	public function setUuid($uuid) {
		$this->uuid = $uuid;
	}

	/**
	 * @return string
	 */
	public function getUuid() {
		return $this->uuid;
	}

	/**
	 * Sets entity path.
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}

	/**
	 * Gets entity path.
	 * @param string $subpath Subpath will be added to the end.
	 * @return string
	 */
	public function getPath(/*$subpath = ''*/) {
		return $this->path/*.($subpath ? "/$subpath" : '')*/;
	}

	/**
	 * Gets entity name.
	 * @return string
	 */
	public function getName() {
		return (($pos = strrpos($this->path, '/')) !== false) ? substr($this->path, $pos + 1) : '';
	}

	/**
	 * @param \DateTime $date
	 */
	public function setCreated(\DateTime $date) {
		$this->created = $date;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreated() {
		return $this->created;
	}

	/**
	 * @return string $userName
	 */
	public function setCreatedBy($userName) {
		$this->createdBy = $userName;
	}

	/**
	 * @return string
	 */
	public function getCreatedBy() {
		return $this->createdBy;
	}

	/**
	 * @param DateTime $date
	 */
	public function setModified(\DateTime $date) {
		$this->modified = $date;
	}

	/**
	 * @return DateTime
	 */
	public function getModified() {
		return $this->modified;
	}

	/**
	 * @return string $userName
	 */
	public function setModifiedBy($userName) {
		$this->modifiedBy = $userName;
	}

	/**
	 * @return string
	 */
	public function getModifiedBy() {
		return $this->modifiedBy;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return get_class($this).'{uuid: '.$this->uuid.', path: '.$this->path.'}';
	}

	/**
	 * Depth in the repository tree
	 * @return int
	 */
	public function getDepth() {
		if (!$this->path)
			return false;
		return substr_count($this->path, '/', 1);
	}

	/**
	 * Returns string for full-text. UUID and created by.
	 * @param array $field_names Field names will be indexed.
	 * @return string
	 */
	public function getTextContent($field_names = array()) {
		$text = "[self:{$this->uuid}]";
		$field_names = array_unique($field_names);
		foreach ($field_names as $name) {
			$value = $this->$name;
			$type = is_object($value) ? get_class($value) : gettype($value);
			if ($value && ($converter = Converter\Factory::get($type, false)))
				$text.= ' '.$converter->convert('string', $value, 'en_US');
		}
		return $text;
	}

	/**
	 * Compares if this content is logically equivalent to another content.
	 * This implementation compares only properties defined via $FIELDS.
	 * @param Vivo\CMS\Model\Entity $entity
	 * @return bool
	 */
// 	public function equals($entity) {
// 		$this_class = get_class($this);
// 		$content_class = get_class($entity);
// 		if ($this_class != $content_class)
// 			return false;
		//@todo: musim mit pristup k field descriptorum :/
// 		foreach (Entity::$FIELDS[$this_class] as $name => $descriptor)
// 			if ($descriptor['comparable'] && ($this->$name != $entity->$name))
// 				return false;
// 		return true;
// 	}

}
