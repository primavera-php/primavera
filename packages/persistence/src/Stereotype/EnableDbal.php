<?php
use PhpBeans\Annotation\Imports;

#[\Attribute(\Attribute::TARGET_CLASS)]
#[Imports([DbalConfiguration::class])]
class EnableDbal
{

}