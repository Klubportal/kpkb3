# COMET API - Complete OpenAPI Schema Analysis

**Date**: October 23, 2025  
**API Version**: v1  
**Base URL**: https://api-hns.analyticom.de

---

## FIFA ID System

All identifiers in the Comet API use FIFA IDs as **64-bit integers (int64)**:

| ID Type | Field Name | Example | Purpose |
|---------|-----------|---------|---------|
| Competition ID | `competitionFifaId` | 100629221 | Identifies a specific competition/league |
| Team ID | `teamFifaId` | 618 | Identifies a team/club within competitions |
| Organization ID | `organisationFifaId` | 598 | Parent organization (national federation, league) |
| Match ID | `matchFifaId` | 102860260 | Unique match identifier |
| Player ID | `personFifaId` | 223034 | Unique person/player identifier |
| Facility ID | `facilityFifaId` | 39933 | Stadium/playing field |
| Sanction ID | `sanctionId` | 367446 | Disciplinary sanction |
| Case ID | `caseFifaId` | 5419390 | Disciplinary case |

---

## Priority Endpoints for NK Prigorje (Org 598)

### ✅ Currently Working

```
GET /api/export/comet/competitions
  ├─ Parameters: active=true, organisationFifaIds=598
  └─ Returns: 11 ACTIVE competitions
```

### 🔄 Core Data Sync Chain

**1. Get All Competitions**
```
GET /api/export/comet/competitions?active=true
Returns: Competition[] with competitionFifaId
```

**2. For Each Competition, Get Teams**
```
GET /api/export/comet/competition/{competitionFifaId}/teams
Returns: CompetitionTeam[] with teamFifaId, organisationFifaId
  └─ Filter by organisationFifaId = 598 for NK Prigorje
```

**3. For Each Competition, Get Matches**
```
GET /api/export/comet/competition/{competitionFifaId}/matches
Returns: Match[] with matchFifaId, matchDay, status, homeTeam, awayTeam
  └─ Can filter by: teamFifaId, matchDay, status, dateFrom, dateTo
```

**4. For Each Match, Get Details**
```
GET /api/export/comet/match/{matchFifaId}
Returns: Match with:
  ├─ matchPhases (FIRST_HALF, SECOND_HALF, EXTRA_TIME, PENALTIES)
  ├─ matchTeams (HOME/AWAY with teamFifaId)
  ├─ matchOfficials (referee, assistants)
  └─ attendance, dateTimeLocal, finalScores

GET /api/export/comet/match/{matchFifaId}/phases
Returns: MatchPhase[] with:
  ├─ phase: "FIRST_HALF", "SECOND_HALF", etc.
  ├─ homeScore, awayScore at each phase
  ├─ regularTime, stoppageTime
  └─ startDateTime, endDateTime

GET /api/export/comet/match/{matchFifaId}/events
Returns: MatchEvent[] with:
  ├─ eventType: "GOAL", "YELLOW", "RED", "SUBSTITUTION", "OWN_GOAL"
  ├─ minute, second, stoppageTime
  ├─ playerFifaId, playerFifaId2 (for substitutions)
  ├─ matchTeam: "HOME" or "AWAY"
  ├─ shirtNumber, penaltyOrder
  └─ localPersonName (player name in local language)

GET /api/export/comet/match/{matchFifaId}/players
Returns: MatchTeamPlayers[] with:
  ├─ teamFifaId, teamNature (HOME/AWAY)
  └─ players[]:
      ├─ personFifaId, shirtNumber
      ├─ startingLineup, captain, goalkeeper
      ├─ played, position
      └─ matchEvents (goals, yellows, reds for this player)
```

**5. For Each Competition, Get Rankings**
```
GET /api/export/comet/competition/{competitionFifaId}/ranking
Returns: Ranking[] with:
  ├─ position, teamFifaId
  ├─ matchesPlayed, wins, draws, losses
  ├─ goalsFor, goalsAgainst, goalDifference
  ├─ points, negativePoints
  └─ winsAfterPenalties, lossesAfterPenalties
```

**6. For Each Competition, Get Top Scorers**
```
GET /api/export/comet/competition/{competitionFifaId}/topScorers
Returns: TopScorer[] with:
  ├─ playerFifaId, goals
  ├─ internationalFirstName, internationalLastName, popularName
  ├─ club (club name), clubId (organisationFifaId)
  ├─ team (team name), teamid (teamFifaId)
  └─ Filter needed: Only include where clubId == 598 (NK Prigorje)
```

**7. For Each Team, Get Players**
```
GET /api/export/comet/team/{teamFifaId}/players?status=ALL
OR
GET /api/export/comet/competition/{competitionFifaId}/{teamFifaId}/players

Returns: PersonExport[] or CompetitionTeamPlayerList with:
  ├─ personFifaId, internationalFirstName, internationalLastName
  ├─ gender, nationality, dateOfBirth
  ├─ placeOfBirth, countryOfBirth
  ├─ playerPosition (Midfielder, Forward, etc.)
  ├─ shirtNumber (when in competition context)
  ├─ status (ACTIVE, INACTIVE)
  └─ localPersonNames[] (names in different languages)
```

**8. For Each Team, Get Officials**
```
GET /api/export/comet/team/{teamFifaId}/teamOfficials?status=ALL
OR
GET /api/export/comet/competition/{competitionFifaId}/{teamFifaId}/teamOfficials

Returns: TeamOfficialRegistration[] or CompetitionTeamOfficialList with:
  ├─ personFifaId, personName
  ├─ role (COACH, ASSISTANT_COACH, GOALKEEPER_COACH, etc.)
  ├─ cometRoleName, cometRoleNameKey
  └─ status (ACTIVE, INACTIVE)
```

---

## Advanced Endpoints

### Match Event Filtering
```
GET /api/export/comet/match/{matchFifaId}/events?eventType=GOAL
  └─ Returns only GOAL events for match

Event Types Available:
  ├─ GOAL, OWN_GOAL
  ├─ YELLOW, RED, SECOND_YELLOW
  ├─ PENALTY, PENALTY_FAILED_MISS, PENALTY_FAILED_SAVE
  ├─ SUBSTITUTION
  ├─ ACCUMULATION_FOUL, EXPULSION
  ├─ TIME_OUT, OFFICIAL_YELLOW
  └─ [Other disciplinary events]
```

### Latest Match Events (Real-time)
```
GET /api/export/comet/match/{matchFifaId}/latest/events?seconds=60
  └─ Returns MatchEvent[] created/updated/deleted in last 60 seconds
  └─ Use for live match updates
```

### Match Last Update
```
GET /api/export/comet/match/{matchFifaId}/lastUpdateDateTime
  └─ Returns timestamp of last update
  └─ Use to check if match data changed before full sync
```

### Disciplinary Cases
```
GET /api/export/comet/competition/{competitionFifaId}/cases
  └─ Returns Case[] for competition

GET /api/export/comet/match/{matchFifaId}/cases
  └─ Returns Case[] for specific match

GET /api/export/comet/case/{caseFifaId}
  └─ Returns single Case with details

GET /api/export/comet/case/{caseFifaId}/sanctions?status=active
  └─ Returns Sanction[] (suspensions, fines, etc.)

Case Object Contains:
  ├─ caseFifaId, description
  ├─ caseDate, status (ACTIVE, INACTIVE)
  ├─ offenderNature (PERSON, ORGANISATION)
  ├─ offenderPersonFifaId or offenderOrganisationFifaId
  ├─ matchFifaId, competitionFifaId
  └─ matchEventFifaId (if case related to specific event)
```

### Player Details
```
GET /api/export/comet/player/{playerFifaId}
Returns: Player object with:
  ├─ person (PersonExport with full details)
  ├─ playerRegistrationList[] (history of registrations)
  │   ├─ organisationFifaId, status (ACTIVE, INACTIVE)
  │   ├─ registrationValidFrom, registrationValidTo
  │   └─ level, discipline, registrationNature
  └─ competitionList[] (competitions played in)
```

### Facilities (Stadiums)
```
GET /api/export/comet/facilities
  └─ Returns Facility[] with all stadiums

GET /api/export/comet/facilities?facilityFifaId=39933
  └─ Returns single facility with details:
      ├─ internationalName, internationalShortName
      ├─ town, address, webAddress
      ├─ phone, fax, email
      ├─ fields[]:
      │   ├─ capacity (stadium capacity)
      │   ├─ groundNature (GRASS, ARTIFICIAL, etc.)
      │   ├─ length, width (in meters)
      │   ├─ latitude, longitude (GPS coordinates)
      │   └─ discipline (FOOTBALL, etc.)
      └─ localNames[]
```

### Images
```
GET /api/export/comet/images/person/{personFifaId}
  └─ Returns player photo as Base64 image

GET /api/export/comet/images/competition/{competitionFifaId}
  └─ Returns competition logo

GET /api/export/comet/images/organization/{organisationFifaId}
  └─ Returns organization logo

GET /api/export/comet/images/update/person/{personFifaId}?date=23.10.2025
  └─ Returns boolean: true if image updated since date
```

### Own Goal Scorers
```
GET /api/export/comet/competition/{competitionFifaId}/ownGoalScorers
  └─ Returns OwnGoalScorer[] (same as TopScorer but for own goals)
```

---

## Query Parameter Combinations

### Example: Get All NK Prigorje Matches in October 2025
```
GET /api/export/comet/competition/{competitionFifaId}/matches
  ?teamFifaId=618
  &dateFrom=01.10.2025
  &dateTo=31.10.2025
  &status=PLAYED
```

### Example: Get Top Scorers for FK Kukesi in Competition
```
GET /api/export/comet/competition/3936145/topScorers
  └─ Filter in application: WHERE clubId == 598 (NK Prigorje)
```

### Example: Get Match Details with Everything
```
1. GET /api/export/comet/match/{matchFifaId}
2. GET /api/export/comet/match/{matchFifaId}/phases
3. GET /api/export/comet/match/{matchFifaId}/events
4. GET /api/export/comet/match/{matchFifaId}/players
5. GET /api/export/comet/match/{matchFifaId}/officials
6. GET /api/export/comet/match/{matchFifaId}/teamOfficials
7. GET /api/export/comet/match/{matchFifaId}/cases
```

---

## Data Filtering Strategy for NK Prigorje

Since the API doesn't have a direct "filter by club" parameter for many endpoints:

### Strategy 1: Filter at Application Level
```
1. Get all competitions (already have 11 ACTIVE)
2. For each competition, get teams
3. Filter teams by organisationFifaId == 598
4. For those teams, get matches
5. Sync matches and details

Advantage: Simple
Disadvantage: More API calls needed
```

### Strategy 2: Use Team IDs Known to be NK Prigorje
```
If we have comet_club_id = 598 in comet_teams table:
  ├─ PRVA ZAGREBAČKA LIGA - teamid = 618
  ├─ 1. ZNL JUNIORI - teamid = 619
  ├─ etc.

Then directly call:
  └─ GET /api/export/comet/team/{teamFifaId}/players
  └─ GET /api/export/comet/team/{teamFifaId}/teamOfficials

Advantage: Fewer total API calls
Disadvantage: Need to know team IDs first
```

---

## Important Field Mappings

### TopScorer to Database
```json
{
  "playerFifaId": 223034,      // → comet_id in comet_players
  "goals": 5,                   // → goals in top_scorers
  "club": "NK Prigorje",        // → club_name
  "clubId": 598,                // → comet_club_id (organisationFifaId)
  "team": "NK Prigorje Markuševec",  // → team_name
  "teamid": 618,                // → comet_team_id
  "internationalFirstName": "Ivan",
  "internationalLastName": "Galić"
  // → full_name in database
}
```

### Match to Database
```json
{
  "matchFifaId": 102860260,         // → comet_id
  "competitionFifaId": 100629221,   // → comet_competition_id
  "matchDay": 4,                    // → match_day
  "dateTimeLocal": "2025-10-23T19:15:40",  // → match_date
  "status": "PLAYED",               // → status
  "homeFinalResult": 2,             // → home_goals
  "awayFinalResult": 1,             // → away_goals
  "matchTeams": [                   // Filter by teamNature
    {
      "teamNature": "HOME",
      "teamFifaId": 618,            // → comet_home_team_id
      "organisationFifaId": 598     // → comet_home_club_id
    },
    {
      "teamNature": "AWAY",
      "teamFifaId": ...,            // → comet_away_team_id
      "organisationFifaId": ...     // → comet_away_club_id
    }
  ]
}
```

### MatchPhase to Database
```json
{
  "matchFifaId": 102860260,
  "phase": "FIRST_HALF",            // → comet_match_phases.phase
  "homeScore": 2,                   // → home_score at this phase
  "awayScore": 1,                   // → away_score at this phase
  "regularTime": 45,                // → regular_time
  "stoppageTime": 2,                // → stoppage_time
  "startDateTime": "2025-10-23T19:15:40",  // → start_date_time
  "endDateTime": "2025-10-23T20:00:40"     // → end_date_time
}
// composite_id = "{matchFifaId}_{phase}"
```

### MatchEvent to Database
```json
{
  "id": 368227,                     // → comet_id
  "matchFifaId": 102860260,         // → comet_match_id
  "matchPhase": "FIRST_HALF",       // → match_phase
  "minute": 37,
  "second": 10,
  "stoppageTime": 2,
  "eventType": "GOAL",              // → event_type
  "eventDetailType": "OWN_GOAL",    // → event_detail_type
  "playerFifaId": 223034,           // → comet_player_id
  "playerFifaId2": 223035,          // → comet_player_id_2 (for substitutions)
  "matchTeam": "HOME",              // → team_side
  "shirtNumber": 10,
  "penaltyOrder": 3                 // For penalty shootouts
}
```

---

## Recommendations

1. **Sync Priority**:
   - ✅ Phase 1: Competitions (done)
   - ✅ Phase 2: Teams, Matches, Rankings (done)
   - 🔄 Phase 3: Match Events, Match Phases
   - 🔄 Phase 4: Players, Officials
   - 🔄 Phase 5: Cases, Sanctions, Facilities

2. **For NK Prigorje Specifically**:
   - Use `comet_club_id = 598` as the main filter
   - Sync all teams where `comet_club_id = 598`
   - For each team, sync players via `/team/{teamFifaId}/players`
   - Use team IDs to get match officials

3. **Real-time Updates**:
   - Use `/match/{matchFifaId}/latest/events?seconds=60` for live scoring
   - Check `/match/{matchFifaId}/lastUpdateDateTime` before full sync
   - Poll every 30 seconds during live matches

4. **Performance**:
   - Cache competition/team data (rarely changes)
   - Update match data every hour when not playing
   - Update live match events every 5-10 seconds during play
