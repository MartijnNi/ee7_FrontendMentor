<?php

namespace AVB\DevelopmentFramework\Commands;

class ExampleCommand extends Command
{
    protected $command = 'example';
    protected $description = 'Return\'s a random quote';
    
    private $quoteList = array(
        "\"Programming is the art of telling a computer what to do, and the art of doing it right.\" - Donald Knuth",
        "\"Good code is its own best documentation.\" - Steve McConnell",
        "\"The best way to predict the future is to invent it.\" - Alan Kay",
        "\"Code never lies, comments sometimes do.\" - Ron Jeffries",
        "\"Debugging is like being a detective in a crime movie where you are also the murderer.\" - Filipe Fortes",
        "\"If debugging is the process of removing software bugs, then programming must be the process of putting them in.\" - Edsger Dijkstra",
        "\"Any fool can write code that a computer can understand. Good programmers write code that humans can understand.\" - Martin Fowler",
        "\"Programming isn't about what you know; it's about what you can figure out.\" - Chris Pine",
        "\"The best way to learn to code is to code.\" - Anonymous",
        "\"The code you write today will haunt you tomorrow.\" - Anonymous",
        "\"Programmers are tools for converting caffeine into code.\" - Anonymous",
        "\"Coding is like writing poetry with a programming language.\" - Anonymous",
        "\"Programmers never die, they just go offline.\" - Anonymous",
        "\"The only way to learn a new programming language is by writing programs in it.\" - Dennis Ritchie",
        "\"Programs must be written for people to read, and only incidentally for machines to execute.\" - Harold Abelson",
        "\"Perl - The only language that looks the same before and after RSA encryption.\" - Keith Bostic",
        "\"Java is to JavaScript as ham is to hamster.\" - Jeremy Keith",
        "\"In order to understand recursion, one must first understand recursion.\" - Anonymous",
        "\"The function of good software is to make the complex appear to be simple.\" - Grady Booch",
        "\"Simplicity is the soul of efficiency.\" - Austin Freeman"
    );
    
    public function execute(array $args): void {
        // Handle the execution of the example command
        // Use $arguments to access command arguments

        echo $this->quoteList[mt_rand(0, count($this->quoteList)-1)]."\n";
    }
}