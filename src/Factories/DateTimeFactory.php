<?php
/**
 * Copyright (c) 2015, Jim DeLois
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author     Jim DeLois <%%PHPDOC_AUTHOR_EMAIL%%>
 * @copyright  2015 Jim DeLois
 * @license    http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version    %%PHPDOC_VERSION%%
 * @link       https://github.com/improvframework/datetime
 * @filesource
 *
 */

namespace Improv\DateTime\Factories;

use Improv\DateTime\DateTimeImmutable;

class DateTimeFactory implements Interfaces\IDateTimeFactory
{
    const DEFAULT_UTC = 'UTC';

    /**
     * @var \DateTimeZone
     */
    private $timezone;

    /**
     * @param null|\DateTimeZone $timezone The default time zone to use during \DateTimeInterface creation
     */
    public function __construct(\DateTimeZone $timezone = null)
    {
        $this->timezone = $timezone ?: new \DateTimeZone(self::DEFAULT_UTC);
    }

    /**
     * {@inheritdoc}
     */
    public function now()
    {
        return $this->createDateTime(DateTimeImmutable::NOW, $this->timezone);
    }

    /**
     * {@inheritdoc}
     */
    public function create($time)
    {
        return $this->createDateTime($time, $this->timezone);
    }

    /**
     * {@inheritdoc}
     */
    public function nowInTimeZone(\DateTimeZone $timezone)
    {
        return $this->createDateTime(DateTimeImmutable::NOW, $timezone);
    }

    /**
     * {@inheritdoc}
     */
    public function createInTimeZone($time, \DateTimeZone $timezone)
    {
        return $this->createDateTime($time, $timezone);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultTimeZone()
    {
        return $this->timezone;
    }

    /**
     * This method exists only to simplify the testing of this class
     *
     * @codeCoverageIgnore
     *
     * @param string $time
     * @param null|\DateTimeZone $timezone
     *
     * @return DateTimeImmutable
     */
    protected function createDateTime($time, \DateTimeZone $timezone = null)
    {
        return new DateTimeImmutable($time, $timezone);
    }
}
