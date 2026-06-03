## 🚀 v1.4.0-beta – Clean Architecture + Token Approval

### ✨ Added
- Token-based approval API (secure, expiring tokens)
- Idempotency guard for workflow start (payload hashing)
- User-level approval validation (security enforcement)
- ApprovalToken domain model

### 🔄 Refactored
- Complete clean architecture separation (Engine / Domain / Adapter)
- Removed HTTP logic from core engine
- Event-driven lifecycle stabilization
- Notification system decoupled via contract

### 🧠 Improved
- Deterministic workflow execution
- Test coverage increased (27 tests)
- Duplicate prevention (approval + notification)
- Batch processing reliability

### ❌ Removed
- Legacy Role dependency (fully IAM-based)
- Controller-driven workflow logic
- MoveToNextStageAction (engine handles flow)

### 🧪 Testing
- Full lifecycle tests (start → approve → complete → reject)
- Token approval test added
- Idempotency + authorization verified

---

🔥 This release transforms the package into a **production-grade workflow engine** ready for SaaS architecture.

---

## [1.6.0] - 2026-06-03

### Added

* Added IAM-driven recipient resolution
* Added ModuleRegistry-based module discovery
* Added assign_type / assign_value recipient support

### Improved

* Improved workflow stage lifecycle tracking
* Improved workflow engine modularity for SaaS integrations
* Improved workflow execution architecture for multi-tenant SaaS platforms

### Fixed

* Removed hardcoded approval recipient fallback
* Fixed workflow recipient assignment flow
* Fixed module resolution consistency across workflow execution

### Technical Notes

* Workflow execution now resolves recipients through a dedicated resolver layer
* Module registration is now driven through ModuleRegistry
* Approval assignment is now compatible with IAM roles, users, and future assignment strategies

## [v1.7.0] - 2026-06-03

### Changed

- Removed tenant coupling from workflow engine
- Removed HasTenant trait
- Removed tenant-aware workflow table requirements
- Restored full package agnosticism

### Architecture

The Approval Engine is now fully reusable across:

- Single-tenant applications
- Enterprise systems
- Government platforms
- Multi-tenant SaaS applications

Multi-tenancy should be implemented by the consuming application rather than the workflow engine itself.

### Notes

This change aligns the Approval Engine with the design philosophy already used by Laravel IAM, where tenancy concerns belong to the application layer.
