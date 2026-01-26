# Release Checklist - ownCloud Guest Plugin PHP 8.4
## Version 1.0.0

**Release Date:** _______________  
**Release Manager:** _______________  
**Modified by:** BW-Tech GmbH

---

## Pre-Release Checklist

### Code Quality
- [x] All unit tests passing (24/24)
- [x] All integration tests passing
- [x] Code review completed
- [x] PHPStan Level 9 passed
- [x] PSR-12 coding standards met
- [x] No deprecated PHP functions
- [x] Complete type annotations
- [x] No PHP warnings or errors
- [x] Code coverage >80%
- [x] All critical bugs fixed (7/7)

### Documentation
- [x] Release notes created and reviewed
- [x] Installation guide created
- [x] Changelog updated
- [x] Migration guide created
- [x] API documentation updated
- [x] README updated
- [x] Examples updated
- [x] Troubleshooting guide created

### Testing
- [x] Unit tests: 24 tests, 53 assertions - PASSED
- [x] Integration tests: All scenarios - PASSED
- [x] Guest registration flow - TESTED
- [x] Guest login flow - TESTED
- [x] WebDAV access - TESTED
- [x] Multiple folder sharing - TESTED
- [x] Whitelist functionality - TESTED
- [x] Email sending - TESTED
- [x] PHP 8.4 compatibility - VERIFIED
- [x] ownCloud Core 10.16.0.0 - VERIFIED

### Security
- [x] Security scan completed
- [x] No known vulnerabilities
- [x] Input validation complete
- [x] XSS protection active
- [x] CSRF protection active
- [x] SQL injection protection active
- [x] Strict typing implemented
- [x] NULL safety ensured

### Performance
- [x] Load tests completed
- [x] Memory usage acceptable
- [x] Response time < 500ms
- [x] Concurrent user tests passed
- [x] Database queries optimized
- [x] Caching configured

### Release Preparation
- [x] Version tag set (1.0.0)
- [x] Git tag created
- [x] Release branch created
- [x] Commit messages reviewed
- [x] All changes committed
- [x] No uncommitted changes
- [x] Working directory clean
- [x] PR merged (if applicable)

---

## Release Process

### 1. Final Verification
- [ ] Run full test suite one last time
```bash
cd /var/www/html/owncloud/apps/guests
./vendor/bin/phpunit
```

- [ ] Verify all documentation is up to date
```bash
grep "1.0.0" RELEASE_NOTES.md
grep "1.0.0" INSTALLATION_GUIDE.md
grep "1.0.0" CHANGELOG.md
```

- [ ] Check version in appinfo/info.xml
```bash
grep "<version>" appinfo/info.xml
```

### 2. Create Git Tag
```bash
# Ensure we're on the correct branch
git checkout php8.4-migration

# Pull latest changes
git pull origin php8.4-migration

# Create annotated tag
git tag -a v1.0.0 -m "Release v1.0.0 - PHP 8.4 Compatible Guest Plugin

- PHP 8.4 compatibility
- 7 critical bug fixes
- 24 unit tests passing
- Production ready"

# Verify tag
git tag -l | grep v1.0.0
git show v1.0.0
```

- [ ] Tag created: _______________

### 3. Push to GitHub
```bash
# Push tag to GitHub
git push origin php8.4-migration
git push origin v1.0.0
```

- [ ] Tag pushed: _______________

### 4. Create GitHub Release
- [ ] Go to: https://github.com/GrossLukas/guest-php84/releases/new
- [ ] Select tag: v1.0.0
- [ ] Release title: v1.0.0 - PHP 8.4 Compatible
- [ ] Copy RELEASE_NOTES.md content to release description
- [ ] Attach binaries if needed:
  - [ ] guest-php84-v1.0.0.zip
- [ ] Mark as pre-release: No
- [ ] Click "Publish release"

- [ ] Release URL: _______________
- [ ] Release published: _______________

### 5. Test Release
- [ ] Download release from GitHub
- [ ] Install in test environment
- [ ] Run all tests
- [ ] Verify functionality
- [ ] Check logs for errors

---

## Post-Release Checklist

### Communication
- [ ] Release announcement published
  - [ ] Blog post
  - [ ] Twitter/X
  - [ ] LinkedIn
  - [ ] ownCloud forum
  
- [ ] Users notified
  - [ ] Email to registered users
  - [ ] Notification in ownCloud admin panel
  - [ ] Update on GitHub releases page

### Documentation Updates
- [ ] Website documentation updated
- [ ] Wiki updated
- [ ] README updated with new version
- [ ] Installation guide reviewed
- [ ] Migration guide verified
- [ ] API documentation updated

### Support Preparation
- [ ] Support team informed
- [ ] Training provided to support staff
- [ ] FAQ updated
- [ ] Known issues documented
- [ ] Support tickets prepared

### Monitoring
- [ ] Release monitoring active
- [ ] Error tracking configured
- [ ] User feedback collection started
- [ ] Performance monitoring active
- [ ] Usage statistics being collected

### Feedback Collection
- [ ] Feedback mechanism set up
- [ ] GitHub issues monitored
- [ ] Support tickets tracked
  - [ ] Number of tickets opened: ______
  - [ ] Number of tickets resolved: ______
  - [ ] Average resolution time: ______
- [ ] User satisfaction survey sent

---

## Verification Checklist

### Installation Verification
- [ ] Clean installation works
- [ ] Upgrade from 0.10.0 works
- [ ] Composer installation works
- [ ] Git clone works
- [ ] Manual installation works

### Functional Verification
- [ ] Guest creation works
- [ ] Guest registration works
- [ ] Guest login works
- [ ] File sharing works
- [ ] Folder sharing works
- [ ] Multiple sharing works
- [ ] WebDAV access works
- [ ] Email notifications work
- [ ] Whitelist works
- [ ] All apps in whitelist accessible

### Compatibility Verification
- [ ] PHP 8.4.x compatible
- [ ] PHP 8.5.x compatible (if available)
- [ ] ownCloud 10.16.0.0 compatible
- [ ] Apache compatible
- [ ] nginx compatible
- [ ] MySQL/MariaDB compatible
- [ ] PostgreSQL compatible
- [ ] SQLite compatible

### Browser Compatibility
- [ ] Chrome/Chromium
- [ ] Firefox
- [ ] Safari
- [ ] Edge
- [ ] Mobile browsers

### Platform Compatibility
- [ ] Ubuntu 20.04+
- [ ] Debian 11+
- [ ] CentOS 8+
- [ ] RHEL 8+
- [ ] Alpine Linux

---

## Rollback Plan

### In case of critical issues:

1. **Immediate Actions**
   - [ ] Pull release from GitHub
   - [ ] Post announcement about rollback
   - [ ] Notify all users via email
   - [ ] Stop distributing release

2. **Rollback Steps**
   ```bash
   # Checkout previous version
   git checkout <previous-tag>
   
   # Create rollback tag
   git tag -a v1.0.1-rollback -m "Rollback v1.0.0"
   
   # Push rollback
   git push origin v1.0.1-rollback
   ```

3. **User Communication**
   - [ ] Issue rollback announcement
   - [ ] Provide rollback instructions
   - [ ] Offer support for affected users
   - [ ] Document rollback reasons

4. **Post-Rollback**
   - [ ] Analyze what went wrong
   - [ ] Fix issues
   - [ ] Re-test thoroughly
   - [ ] Prepare new release

---

## Metrics to Track

### Release Metrics
- [ ] Number of downloads in first 24h: ______
- [ ] Number of downloads in first week: ______
- [ ] Number of installations: ______
- [ ] Number of upgrades from 0.10.0: ______
- [ ] Number of bug reports: ______
- [ ] Number of feature requests: ______

### Quality Metrics
- [ ] Crash rate: ______
- [ ] Error rate: ______
- [ ] User satisfaction score: ______
- [ ] Average response time: ______
- [ ] System uptime: ______

### Support Metrics
- [ ] Number of support tickets: ______
- [ ] Average ticket resolution time: ______
- [ ] Number of critical issues: ______
- [ ] Number of resolved issues: ______

---

## Sign-Off

### Release Team
- **Release Manager:** _______________  Date: _______________
- **Lead Developer:** _______________  Date: _______________
- **QA Lead:** _______________  Date: _______________
- **Documentation:** _______________  Date: _______________
- **Support Lead:** _______________  Date: _______________

### Management Sign-Off
- **Product Owner:** _______________  Date: _______________
- **Engineering Manager:** _______________  Date: _______________
- **Security Officer:** _______________  Date: _______________

### Final Approval
- **Approved for Release:** [ ] Yes  [ ] No
- **Approval Date:** _______________
- **Approved By:** _______________

---

## Notes

### Release Notes
```
Add any additional notes about the release process here.
```

### Issues Encountered
```
Document any issues encountered during the release process.
```

### Lessons Learned
```
Document lessons learned for future releases.
```

---

## Quick Reference

### Important Commands
```bash
# Run tests
./vendor/bin/phpunit

# Check version
grep "<version>" appinfo/info.xml

# Create tag
git tag -a v1.0.0 -m "Release v1.0.0"

# Push tag
git push origin v1.0.0

# View tag
git show v1.0.0

# Delete tag (if needed)
git tag -d v1.0.0
git push origin :refs/tags/v1.0.0
```

### Important URLs
- Repository: https://github.com/GrossLukas/guest-php84
- Releases: https://github.com/GrossLukas/guest-php84/releases
- Issues: https://github.com/GrossLukas/guest-php84/issues
- Pull Requests: https://github.com/GrossLukas/guest-php84/pulls
- Wiki: https://github.com/GrossLukas/guest-php84/wiki

### Important Contacts
- **BW-Tech GmbH:** info@bw-tech.de
- **Support:** support@bw-tech.de
- **GitHub Issues:** https://github.com/GrossLukas/guest-php84/issues

---

**End of Release Checklist**