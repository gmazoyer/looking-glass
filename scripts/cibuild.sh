#!/bin/bash

EXIT=0

# Output a line prefixed with a timestamp
info() {
    echo "$(date +'%F %T') |"
}

# Track number of seconds required to run script
START=$(date +%s)
echo "$(info) starting build checks."

# Syntax check all php files
SYNTAX=$(find . -name "*.php" -type f -exec php --syntax-check {} \; > /dev/null)
if [[ ! -z ${SYNTAX} ]]; then
  echo -e "${SYNTAX}"
  echo -e "\n$(info) detected one or more syntax errors, failing build."
  EXIT=1
fi

# Show build duration
END=$(date +%s)
echo "$(info) exiting with code ${EXIT} after $((${END} - ${START})) seconds."

exit ${EXIT}
