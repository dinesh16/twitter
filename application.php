#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';
require_once __DIR__ . '/TwitterOAuth/TwitterOAuth.php';
require_once __DIR__ . '/TwitterOAuth/Exception/TwitterException.php';

use TwitterOAuth\TwitterOAuth;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Twitter extends Command
{
    private $connection = null;
    private $screen_name = '';

    protected function configure() {
        $this
            ->setName('Twitter:Account')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Which account you want to check?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $this->screen_name = $input->getArgument('name');

        $this->connection = new TwitterOAuth($this->getConfig());
        $bearer_token = $this->connection->getBearerToken();
        $keywords = $this->getHashtags();
        if(!empty($keywords)){
            arsort($keywords);
            $output->writeln("");
            foreach($keywords as $hash_tag=>$count){
                $output->writeln("$hash_tag, $count");
            }
        }
    }

    private function getConfig(){
        return array(
            'consumer_key'       => 'oeJJmbLCssG6d3cdNxNJIzzP2', // API key
            'consumer_secret'    => 'Y92yPn1FijE0wHlcbJaLy08wGNryIOPefxQA3Yjvi4DLwmmfbw', // API secret
            'oauth_token'        => '', // not needed for app only
            'oauth_token_secret' => '',
            'output_format'      => 'object'
        );
    }

    private function getTweets(){
        $params = array(
            'screen_name' => $this->screen_name,
            'count' => 100
        );
        return $this->connection->get('statuses/user_timeline', $params);
    }

    private function getHashtags(){
        $keywords = array();
        $response = $this->getTweets();
        if(!empty($response)) {
            foreach($response as $res){
                foreach($res->entities->hashtags as $hash_tags){
                    if(array_key_exists($hash_tags->text, $keywords)){
                        $keywords[$hash_tags->text] += 1;
                    } else {
                        $keywords[$hash_tags->text] = 1;
                    }
                }
            }
        }
        return $keywords;
    }
}

$application = new Application();
$application->add(new Twitter());
$application->run();
