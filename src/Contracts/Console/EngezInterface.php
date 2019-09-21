<?php       
namespace HZ\Illuminate\Mongez\Contracts\Console;

interface EngezInterface
{
    /**
     * Initialize the module segment builder
     * 
     * @return void
     */
    public function init();
    
    /**
     * Validate the passed argument to the module segment builder
     * 
     * @return void
     */
    public function validateArguments();
    
    /**
     * Create the module segment
     * 
     * @return void
     */
    public function create();
}