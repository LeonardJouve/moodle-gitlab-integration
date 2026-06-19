<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Display information about all the mod_gitlab modules in the requested course.
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_gitlab\local;

use stdClass;
use Throwable;

class Group {
    public static function group(int $group_id) {
        global $DB;

        return $DB->get_record_sql("
            SELECT
                g.id,
                g.repository_id,
                COUNT(m.user_id) AS member_count
            FROM {gitlab_groups} g
            LEFT JOIN {gitlab_group_members} m
                ON m.group_id = g.id
            WHERE g.id = :group_id
            GROUP BY g.id
        ", [
            'group_id' => $group_id,
        ]);
    }

    public static function has_group(int $module_id, int $user_id): bool {
        return Group::user_group($module_id, $user_id) !== null;
    }

    public static function user_group(int $module_id, int $user_id): ?int {
        global $DB;

        $group = $DB->get_record_sql("
            SELECT g.id
            FROM {gitlab_group_members} m
            JOIN {gitlab_groups} g
                ON g.id = m.group_id
            WHERE
                g.module_id = :module_id
                AND m.user_id = :user_id
        ", [
            'module_id' => $module_id,
            'user_id' => $user_id,
        ]);

        return $group ?
            $group->id :
            null;
    }

    public static function get_groups(int $module_id) {
        global $DB;
        
        $groups = $DB->get_records_sql("
            SELECT
                g.id,
                g.repository_id,
                COALESCE(array_agg(u.username) FILTER (WHERE u.username IS NOT NULL), ARRAY[]::text[]) AS members
            FROM {gitlab_groups} g
            LEFT JOIN {gitlab_group_members} m
                ON m.group_id = g.id
            LEFT JOIN {user} u
                ON u.id = m.user_id
            WHERE g.module_id = :module_id
            GROUP BY g.id
        ", [
            'module_id' => $module_id,
        ]);

        return array_values($groups);
    }

    public static function create_group(int $module_id, int $user_id, int $repository_id): ?int {
        global $DB;

        if (Group::has_group($module_id, $user_id)) {
            return null;
        }

        $group = new stdClass();
        $group->module_id = $module_id;
        $group->repository_id = $repository_id;

        $group_id = $DB->insert_record('gitlab_groups', $group);

        return $group_id;
    }

    public static function join_group(int $module_id, int $group_id, int $user_id, int $max_member): bool {
        global $DB;
    
        if (Group::has_group($module_id, $user_id)) {
            return false;
        }

        $group = Group::group($group_id);
        if ($group->member_count >= $max_member) {
            return false;
        }

        $member = new stdClass();
        $member->group_id = $group_id;
        $member->user_id  = $user_id;

        return $DB->insert_record('gitlab_group_members', $member);
    }

    public static function leave_group(int $module_id, int $user_id): bool {
        global $DB;

        $group_id = Group::user_group($module_id, $user_id);
        if (!$group_id) {
            return false;
        }

        $ok = $DB->delete_records('gitlab_group_members', [
            'group_id' => $group_id,
            'user_id'  => $user_id
        ]);

        return $ok;
    }

    public static function set_group_members(array $members, int $max_member, int $group_id): bool {
        global $DB;

        if (count($members) > $max_member) {
            return false;
        }

        $transaction = $DB->start_delegated_transaction();

        try {
            list($not_in_sql, $params) = $DB->get_in_or_equal($members, SQL_PARAMS_NAMED, '', false, NULL);

            $params['group_id'] = $group_id;

            $DB->delete_records_select(
                'gitlab_group_members',
                "group_id = :group_id AND user_id $not_in_sql",
                $params,
            );

            foreach ($members as $user_id) {
                $exists = $DB->record_exists('gitlab_group_members', [
                    'group_id' => $group_id,
                    'user_id'  => $user_id
                ]);
                if ($exists) {
                    continue;
                }

                $member = new stdClass();
                $member->group_id = $group_id;
                $member->user_id  = $user_id;

                $DB->insert_record('gitlab_group_members', $member);
            }

            $transaction->allow_commit();

            return true;
        } catch (Throwable $e) {
            $transaction->rollback($e);

            return false;
        }
    }

    public static function delete_group(int $group_id): bool {
        global $DB;
    
        $DB->delete_records('gitlab_group_members', ['group_id' => $group_id]);
        $DB->delete_records('gitlab_groups', ['id' => $group_id]);

        return true;
    }
}
