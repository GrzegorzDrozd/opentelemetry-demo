#!/bin/bash

# from: https://develop.sentry.dev/self-hosted/
VERSION=$(curl -Ls -o /dev/null -w %{url_effective} https://github.com/getsentry/self-hosted/releases/latest)
VERSION=${VERSION##*/}
git clone https://github.com/getsentry/self-hosted.git sentry
cd sentry
git checkout ${VERSION}
REPORT_SELF_HOSTED_ISSUES=0 ./install.sh