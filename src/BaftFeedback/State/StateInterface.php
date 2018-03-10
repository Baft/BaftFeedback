<?php
namespace BaftFeedback\State;

interface StateInterface{
	public static $_state;

	public function setState();
	public function getState();

	public function onState();

}