<?php

namespace R3H6\T3devtools\Seeder;

class FileReferenceProvider extends \Faker\Provider\Base
{
    /**
     * @var \R3H6\T3devtools\Seeder\Faker
     */
    protected $generator;

    public function __construct(Faker $faker)
    {
        $this->generator = $faker;
    }

    public function fal($fieldName, Seed $seed, $files)
    {
        $uids = [];
        foreach ($files as $file) {
            $uid = uniqid('NEW');
            $data = $this->generator->getData();
            $data['sys_file_reference'][$uid] = [
                'pid' => $seed['pid'],
                'table_local' => 'sys_file',
                'uid_local' => $file->getUid(),
                'tablenames' => $seed->getTable(),
                'uid_foreign' => $seed->getIdentifier(),
                'fieldname' => $fieldName,
            ];
            $uids[] = $uid;
        }
        return implode(',', $uids);
    }
}
