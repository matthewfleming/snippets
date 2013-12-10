<?php

/**
 * Holiday hold start date validator
 */
class HoldValidator
{
    /**
     * DTI system enforced time is 12pm, allow 30 mins for processing
     */
    const CUTOFF_TIME = "11:30AM";

    /**
     * Minimum waiting period to action a stop, if it is requested before the cutoff time.
     */
    const MIN_WAITING_PERIOD = 1;

    private $stopDate;
    private $now;
    private $cutOff;
    private $earliestStop;
    private $isValid;

    public function __construct(\DateTime $stopDate)
    {
        $this->stopDate = clone $stopDate;
        $this->now = new \DateTime();
        $this->cutOff = new \DateTime($this->now->format('Y-m-d') . ' ' . self::CUTOFF_TIME);
        $this->earliestStop = $this->calculateEarliestStop();
    }

    /**
     * Returns true if the stop date begins on or after the required waiting period
     * @return bool
     */
    public function isValid()
    {
        if ($this->isValid === null) {
            $this->isValid = ($this->earliestStop <= $this->stopDate);
        }
        return $this->isValid;
    }

    /**
     * Returns the earliest stop date
     * @return \DateTime
     */
    public function getEarliestStop()
    {
        return $this->earliestStop;
    }

    /**
     * Returns a new DateTime object with the waiting period rules applied
     * @return \DateTime
     */
    private function calculateEarliestStop()
    {
        $earliest = new DateTime($this->now->format('Y-m-d'));

        $period = self::MIN_WAITING_PERIOD;
        if ($this->now > $this->cutOff) {
            $period++;
        }
        return $earliest->add(new DateInterval('P' . $period . 'D'));
    }

}

// test
$date = new DateTime('yesterday');
echo 'Requested stop | Is valid';
echo "\n--------------------------";
foreach (range(0, 4) as $i) {
    $validator = new HoldValidator($date);
    echo "\n" . $date->format('Y-m-d');
    echo "     | " . ($validator->isValid() ? 'Yes' : 'No');
    $date->add(new DateInterval('P1D'));
}
echo "\n\nTime now:           " . date('g:iA');
echo "\nCutoff time:        " . HoldValidator::CUTOFF_TIME;
echo "\nEarliest stop date: " . $validator->getEarliestStop()->format('Y-m-d');

