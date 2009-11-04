#!/bin/bash

# This script creates symlinks from the local GIT repo into your EE2 install. It also copies some of the extension icons.

dirname=`dirname "$0"`

echo ""
echo "You are about to create symlinks for NSM Multi Language"
echo "------------------------------------------------------"
echo ""
echo "Enter the full path to your ExpressionEngine 2 install without a trailing slash [ENTER]:"
read ee_path
echo "Enter your ExpressionEngine 2 system folder name [ENTER]:"
read ee_system_folder

ln -s "system/expressionengine/third_party/ext.nsm_multilanguage" "$ee_path/$ee_system_folder/expressionengine/third_party/ext.nsm_multilanguage"
ln -s "system/expressionengine/language/nsm.languages" "$ee_path/$ee_system_folder/expressionengine/language/nsm.languages"
