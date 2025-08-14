# Academiadepolitie.com MCP Remote Server

A Remote Model Context Protocol (MCP) server for Academiadepolitie.com - Romania's leading educational platform for police academy entrance exam preparation. Supporting over **50,000 students** with AI-powered tutoring and personalized learning analytics.

## 🚀 What is Remote MCP?

Unlike Local MCP servers that run on your machine, this Remote MCP server runs on dedicated infrastructure and uses HTTP/SSE transport with OAuth 2.1 authentication. This enables:
- **Scalable access** for multiple users
- **Secure authentication** with your Academiadepolitie.com account  
- **Real-time data** directly from the platform
- **Production-grade reliability**

## 🎓 Educational Platform

### Subjects Covered (20 Active Categories)

#### Core Legal Studies
- **Criminal Law - General Part** (Drept Penal - Partea Generală) - Fundamental concepts, legal principles
- **Criminal Law - Special Part** (Drept Penal - Partea Specială) - Specific crimes, infractions, penalties  
- **Criminal Procedure Law** (Drept Procesual Penal) - Court procedures, evidence, investigation
- **Constitutional Law** (Drept Constituțional) - Romanian constitution, state organization
- **General Theory of Law** (Teoria Generală a Dreptului) - Legal concepts, sources of law

#### MAI Specialized Legislation  
- **MAI Legislation** (Legislația MAI) - Ministry of Internal Affairs specific laws
- **Information Security** (Protecția Informațiilor Clasificate) - Classified information protocols
- **GDPR** - Data protection regulation compliance

#### General Education & Skills
- **Romanian History** (Istoria Românilor) - National history, key events
- **Languages** - Romanian, English, French, German proficiency
- **Mathematics & Physics** - Quantitative reasoning and applications
- **Logical Reasoning** (Raționament Logic) - Critical thinking, formal logic
- **Civic Education** (Ed. Civică) - Citizenship, democratic principles

#### Practical Training
- **Psychology** (Psihologic) - Psychological evaluation, behavioral assessment
- **Applied Training** (Traseu Aplicativ) - Practical exercises, real scenarios

### Key Features
- 🔍 **Comprehensive learning progress analysis**
- 📚 **Fuzzy search across 5,000+ educational articles**
- 🧠 **Personalized AI-generated quizzes and study tools**
- 👥 **Peer collaboration and challenge systems**
- 📊 **Detailed performance analytics and gap identification**

## 🎯 Who Benefits from This MCP Server

### Primary Users: MAI Institution Admission Candidates
Students preparing for entrance exams to Romania's Ministry of Internal Affairs educational institutions:

#### **Police Academy & Officer Programs**
- **Police Academy** (Academia de Politie) - Bachelor's degree officer training
- **Professional Master's Program** (Master Profesional Academia de Politie) - Advanced law enforcement leadership
- **Firefighters Program** (Pompieri Academia de Politie) - Fire safety and emergency response specialization

#### **Police Agent Schools**
- **Police Agent School Campina** - Regional agent training program
- **Police Agent School Cluj-Napoca** - Northern region agent training

#### **Specialized Law Enforcement**
- **Border Police School Oradea** - Border control and frontier management

#### **Gendarmerie Forces**
- **Military Gendarmerie School Dragasani** - NCO and specialized training
- **Military Gendarmerie School Falticeni** - Regional NCO program

#### **Penitentiary System**
- **ANP Targu Ocna** - Prison administration and corrections training

### Secondary Users
- **Educational Institutions** - AI-enhanced tutoring for law enforcement curricula
- **Study Groups** - Collaborative learning with peer matching and challenges  
- **Tutors & Instructors** - Personalized learning analytics and progress tracking
- **Career Professionals** - Continuing education and skill development
- **Researchers** - Educational data analysis and learning pattern insights

*Supporting **30,000+ students** across all MAI educational institutions*

## 🔧 Claude Desktop Configuration

### Remote MCP Setup (Recommended)

Add the following configuration to your `claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "academiadepolitie-remote": {
      "transport": {
        "type": "http",
        "url": "https://mcp.academiadepolitie.com:8443/mcp"
      }
    }
  }
}
```

### Setup Steps

1. **Access the Remote Server**: Visit https://mcp.academiadepolitie.com:8443
2. **OAuth Authentication**: Log in with your Academiadepolitie.com account
3. **Claude Integration**: The server will guide you through connecting with Claude
4. **Start Using**: Access all MCP tools directly from Claude Desktop or Claude Web

## 🔐 Authentication

This server uses **OAuth 2.1 with PKCE** for secure authentication:
- No need to manually manage tokens
- Secure browser-based login flow
- Automatic token refresh
- Rate limiting and CORS protection

## 🛠️ Available MCP Tools

Once connected, you'll have access to:

### `get_student_data`
- Retrieve comprehensive student performance analytics
- Learning progress tracking
- Strengths and weaknesses analysis

### `search_articles` 
- Fuzzy search across educational content
- Advanced filtering by subject and difficulty
- Relevance scoring and recommendations

### `get_article_content`
- Access detailed lesson content
- Progressive learning with pagination
- Rich media support

### `add_note`
- Create and manage study notes
- Link notes to specific topics
- Collaborative note sharing

### `send_challenge`
- Create peer challenges
- Gamified learning experiences
- Performance comparisons

### `update_reading_progress`
- Track reading completion
- Adaptive learning paths
- Progress synchronization

## 🌐 Server Infrastructure

- **Production URL**: https://mcp.academiadepolitie.com:8443
- **Protocol**: HTTP/SSE with OAuth 2.1
- **Uptime**: 99.9%+ availability
- **Support**: Both Claude Desktop and Claude Web

## 📋 System Requirements

- Claude Desktop (latest version) or Claude Web access
- Internet connection for OAuth authentication
- Active Academiadepolitie.com account

## 🤝 Support

- **Documentation**: Visit the server URL for interactive docs
- **Issues**: Create an issue in this repository
- **Platform Support**: Contact support@academiadepolitie.com

## 📄 License

MIT License - see LICENSE file for details.

---

**🎯 Transform your Admission Exam preparation with AI-powered learning tools - connect your Academiadepolitie.com knowledge to Claude's intelligence!**