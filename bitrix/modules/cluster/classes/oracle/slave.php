<?php

class CClusterSlave
{
	public static function Stop($node_id)
	{
		return true;
	}
	public static function GetRandomNode()
	{
		return false;
	}
}
