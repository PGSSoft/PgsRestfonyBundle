<?php

namespace Pgs\RestfonyBundle\Generator;

interface ActionNamesInterface
{
    const GET_LIST = 'getList';
    const GET = 'getOne';
    const POST = 'post';
    const PUT = 'put';
    const DELETE = 'delete';
    const PATCH = 'patch';
    const NEW_FORM = 'newForm';
    const EDIT_FORM = 'editForm';
}
