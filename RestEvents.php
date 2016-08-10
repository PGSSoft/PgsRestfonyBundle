<?php

namespace Pgs\RestfonyBundle;

final class RestEvents
{
    const LIST_ACTION_BEFORE_PAGINATION = 'list_action_before_pagination';
    const LIST_ACTION_AFTER_PAGINATION = 'list_action_after_pagination';
    const POST_ACTION_PRE_SUBMIT = 'post_action_pre_submit';
    const POST_ACTION_POST_VALIDATION = 'post_action_post_validation';
    const POST_ACTION_POST_PERSIST = 'post_action_post_persist';
    const POST_ACTION_VALIDATION_ERROR = 'post_action_validation_error';
    const PUT_ACTION_PRE_SUBMIT = 'put_action_pre_submit';
    const PUT_ACTION_POST_VALIDATION = 'put_action_post_validation';
    const PUT_ACTION_POST_PERSIST = 'put_action_post_persist';
    const PUT_ACTION_VALIDATION_ERROR = 'put_action_validation_error';
    const GET_ACTION_POST_LOAD = 'get_action_post_load';
    const NEW_ACTION_LOAD = 'new_action_load';
    const EDIT_ACTION_LOAD = 'edit_action_load';
    const DELETE_ACTION_LOAD_ENTITY = 'delete_action_load_entity';
    const DELETE_ACTION_PRE_DELETE = 'delete_action_pre_delete';
    const DELETE_ACTION_POST_DELETE = 'delete_action_post_delete';
    const HANDLE_VIEW = 'handle_view';
    const PRE_PATCHED_ACTION = 'pre_patched_action';
    const POST_PATCHED_ACTION = 'post_patched_action';
}
